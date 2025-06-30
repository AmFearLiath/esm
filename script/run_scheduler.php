<?php
set_time_limit(0);

$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
$pidFile = __DIR__ . '/pids/scheduler.pid';
$logFile = __DIR__ . '/logs/scheduler.log';

function logmsg($msg) {
    global $logFile;
    file_put_contents($logFile, "[".date('Y-m-d H:i:s')."] $msg\n", FILE_APPEND);
}

file_put_contents($pidFile, getmypid());
logmsg("EVENT:writePID: " . getmypid());

function getInterval($schedule) {
    // Unterstützt Formate wie "every 1800s", "1800s", "02:00, 14:00"
    if (preg_match('/every\s+(\d+)\s*s?/i', $schedule, $m)) {
        return (int)$m[1];
    }
    if (preg_match('/^(\d+)\s*s?$/i', $schedule, $m)) {
        return (int)$m[1];
    }
    // Fallback: 60 Sekunden
    return 60;
}

function getNextTime($schedule) {
    // Unterstützt Uhrzeiten wie "02:00, 14:00"
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
    logmsg("Scheduler-Loop #$loop gestartet");

    // --- Prüfe Konfiguration ---
    $required = ['ftpServer','ftpPort','ftpUser','ftpPass','ftpDir','scheduleSavegame','scheduleSchedule'];
    $missing = [];
    foreach ($required as $key) {
        if (empty($config[$key])) $missing[] = $key;
    }
    if ($missing) {
        logmsg("EVENT:configMissing:" . implode(', ', $missing));
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

    // --- Datei 3ad85aea-index holen ---
    $remoteDir = rtrim($config['ftpDir'], '/');
    $remoteFile = $remoteDir ? "$remoteDir/3ad85aea-index" : "3ad85aea-index";
    $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('scheduler_', true) . '.json';

    if (!@ftp_get($ftp, $tmpFile, $remoteFile, FTP_BINARY)) {
        logmsg("ERROR:downloadFailed: $remoteFile");
        ftp_close($ftp);
        sleep(30);
        continue;
    }

    // --- JSON bearbeiten ---
    $json = json_decode(file_get_contents($tmpFile), true);
    if (!is_array($json)) {
        logmsg("ERROR:invalidConfig:$remoteFile.");
        ftp_close($ftp);
        unlink($tmpFile);
        sleep(30);
        continue;
    }
    $json['latest'] = (int)$config['scheduleSavegame'];
    file_put_contents($tmpFile, json_encode($json, JSON_PRETTY_PRINT));
    logmsg("EVENT:latestSet: " . $json['latest']);

    // --- Datei zurück auf FTP ---
    if (!@ftp_put($ftp, $remoteFile, $tmpFile, FTP_BINARY)) {
        logmsg("EVENT:uploadFailed:$remoteFile");
        ftp_close($ftp);
        unlink($tmpFile);
        sleep(30);
        continue;
    }
    logmsg("EVENT:fileUpdated:$remoteFile");

    // Nach dem Bearbeiten der 3ad85aea-index:
    $scheduleLocalSavegame = $config['scheduleLocalSavegame'] ?? '';
    $scheduleSavegame = $config['scheduleSavegame'] ?? '';
    if ($scheduleLocalSavegame && is_file($scheduleLocalSavegame)) {
        // Ziel-Dateiname auf dem Server bestimmen
        $remoteDir = rtrim($config['ftpDir'], '/');
        $remoteSavegame = $remoteDir ? "$remoteDir/3ad85aea" : "3ad85aea";
        if ($scheduleSavegame !== "0") {
            $remoteSavegame .= "-$scheduleSavegame";
        }
        // Vorher ggf. löschen
        @ftp_delete($ftp, $remoteSavegame);
        // Hochladen
        if (@ftp_put($ftp, $remoteSavegame, $scheduleLocalSavegame, FTP_BINARY)) {
            logmsg("EVENT:savegameUploaded:$remoteSavegame");
        } else {
            logmsg("EVENT:savegameUploadFailed:$scheduleLocalSavegame");
        }
    } else {
        logmsg("EVENT:savegameNotFound:$scheduleLocalSavegame");
    }

    ftp_close($ftp);
    unlink($tmpFile);

    logmsg("EVENT:schedulerTaskCompleted");

    // --- Zeitsteuerung ---
    $schedule = $config['scheduleSchedule'];
    if (preg_match('/^(\d+)\s*s?$/i', trim($schedule)) || preg_match('/every\s+(\d+)\s*s?/i', trim($schedule))) {
        $interval = getInterval($schedule);
        logmsg("EVENT:schedulerNextInterval: $interval");
        sleep($interval);
    } elseif (preg_match('/^(\d{1,2}:\d{2})(,\s*\d{1,2}:\d{2})*$/', trim($schedule))) {
        $next = getNextTime($schedule);
        $wait = $next - time();
        logmsg("EVENT:schedulerNextTime: $wait");
        if ($wait > 0) sleep($wait);
    } else {
        logmsg("EVENT:unknownScheduleFormat");
        sleep(60);
    }
}

logmsg("EVENT:scheduleProcessEnded");
@unlink($pidFile);