<?php
set_time_limit(0);

$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
$pidFile = __DIR__ . '/pids/backup.pid';
$logFile = __DIR__ . '/logs/backup.log';

function logmsg($msg) {
    global $logFile;
    file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] $msg\n", FILE_APPEND);
}

file_put_contents($pidFile, getmypid());
logmsg("EVENT:writePID: " . getmypid());

function getInterval($schedule) {
    if (preg_match('/every\s+(\d+)\s*s?/i', $schedule, $m)) return (int)$m[1];
    if (preg_match('/^(\d+)\s*s?$/i', $schedule, $m)) return (int)$m[1];
    return 60;
}
function getNextTime($schedule) {
    $now = time();
    $times = [];
    foreach (explode(',', $schedule) as $t) {
        $t = trim($t);
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $t, $m)) {
            $h = (int)$m[1];
            $min = (int)$m[2];
            $candidate = mktime($h, $min, 0);
            if ($candidate <= $now) $candidate = strtotime('+1 day', $candidate);
            $times[] = $candidate;
        }
    }
    if ($times) return min($times);
    return false;
}

$loop = 0;
while (file_exists($pidFile)) {
    file_put_contents($pidFile, getmypid());    
    $loop++;
    logmsg("EVENT:backupLoopStarted:$loop");

    // --- Prüfe Konfiguration ---
    $required = ['ftpServer','ftpPort','ftpUser','ftpPass','ftpDir','backupLocalPath','backupRotation','backupZip','backupSchedule'];
    $missing = [];
    foreach ($required as $key) {
        if (empty($config[$key])) $missing[] = $key;
    }
    if ($missing) {
        logmsg("ERROR:configMissing:" . implode(', ', $missing));
        sleep(30);
        continue;
    }

    // --- FTP Verbindung ---
    $ftp = @ftp_connect($config['ftpServer'], (int)$config['ftpPort'], 10);
    if (!$ftp) {
        logmsg("ERROR:ftpConnectionFail");
        sleep(30);
        continue;
    }
    if (!@ftp_login($ftp, $config['ftpUser'], $config['ftpPass'])) {
        logmsg("ERROR:ftpLoginFail");
        ftp_close($ftp);
        sleep(30);
        continue;
    }
    ftp_pasv($ftp, true);
    logmsg("EVENT:ftpConnectionOk");

    // --- Dateien finden ---
    $remoteDir = rtrim($config['ftpDir'], '/');
    $baseFiles = ['3ad85aea', '3ad85aea-index'];
    for ($i = 1; $i <= 9; $i++) $baseFiles[] = "3ad85aea-$i";
    $foundFiles = [];
    foreach ($baseFiles as $file) {
        $remoteFile = $remoteDir ? "$remoteDir/$file" : $file;
        if (@ftp_size($ftp, $remoteFile) > -1) $foundFiles[] = $file;
    }
    if (empty($foundFiles)) {
        logmsg("ERROR:backupNoFiles");
        ftp_close($ftp);
        sleep(30);
        continue;
    }

    // --- Backup-Verzeichnis ---
    $backupDir = rtrim($config['backupLocalPath'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . date('Ymd_His');
    if (!is_dir($backupDir) && !mkdir($backupDir, 0777, true)) {
        logmsg("ERROR:backupDirFailed:$backupDir");
        ftp_close($ftp);
        sleep(30);
        continue;
    }

    // --- Dateien herunterladen ---
    $downloaded = [];
    foreach ($foundFiles as $file) {
        $remoteFile = $remoteDir ? "$remoteDir/$file" : $file;
        $localFile = $backupDir . DIRECTORY_SEPARATOR . $file;
        if (@ftp_get($ftp, $localFile, $remoteFile, FTP_BINARY)) {
            $downloaded[] = $file;
            logmsg("EVENT:fileDownloaded:$file");
            logmsg("PROGRESS:" . count($downloaded) . "/" . count($foundFiles));
        } else {
            logmsg("ERROR:downloadFailed:$file");
        }
    }
    ftp_close($ftp);

    // --- Optional ZIP ---
    if (!empty($downloaded) && $config['backupZip'] == "1") {
        $zipFile = $backupDir . ".zip";
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            foreach ($downloaded as $f) {
                $filePath = $backupDir . DIRECTORY_SEPARATOR . $f;
                if (file_exists($filePath)) $zip->addFile($filePath, $f);
            }
            $zip->close();
            logmsg("EVENT:zipCreated:$zipFile");
            foreach ($downloaded as $f) {
                $filePath = $backupDir . DIRECTORY_SEPARATOR . $f;
                if (file_exists($filePath)) unlink($filePath);
            }
            @rmdir($backupDir);
        } else {
            logmsg("ERROR:zipFailed");
        }
    } else {
        logmsg("EVENT:backupFolderSaved:$backupDir");
    }

    // --- Backup-Rotation ---
    $rotation = (int)$config['backupRotation'];
    $allBackups = glob(rtrim($config['backupLocalPath'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*');
    usort($allBackups, function($a, $b) { return filemtime($b) - filemtime($a); });
    foreach (array_slice($allBackups, $rotation) as $old) {
        if (is_dir($old)) {
            array_map('unlink', glob("$old/*.*"));
            @rmdir($old);
            logmsg("EVENT:oldBackupDeletedFolder:$old");
        }
        if (is_file($old)) {
            unlink($old);
            logmsg("EVENT:oldBackupDeletedFile:$old");
        }
    }

    logmsg("EVENT:backupComplete");

    // --- Zeitsteuerung ---
    $schedule = $config['backupSchedule'];
    if (preg_match('/^(\d+)\s*s?$/i', trim($schedule)) || preg_match('/every\s+(\d+)\s*s?/i', trim($schedule))) {
        $interval = getInterval($schedule);
        $nextTime = date('H:i:s', time() + $interval);
        logmsg("NEXT:nextBackupAt:$nextTime");
        sleep($interval);
    } elseif (preg_match('/^(\d{1,2}:\d{2})(,\s*\d{1,2}:\d{2})*$/', trim($schedule))) {
        $next = getNextTime($schedule);
        $wait = $next - time();
        $nextTime = date('H:i:s', $next);
        logmsg("NEXT:nextBackupAt:$nextTime");
        if ($wait > 0) sleep($wait);
    } else {
        logmsg("NEXT:nextBackup60");
        sleep(60);
    }
}

logmsg("EVENT:backupProcessEnded");
@unlink($pidFile);