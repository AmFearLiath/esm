<?php
class Backuper {
    private $config;
    private $logFile;
    private $pidFile;

    public function __construct($config) {
        $this->config = $config;
        $this->logFile = __DIR__ . '/logs/backup.log';
        $this->pidFile = __DIR__ . '/pids/backup.pid';
    }

    public function validateConfig() {
        $missing = [];
        foreach (['ftpServer','ftpPort','ftpUser','ftpPass','ftpDir','backupLocalPath','backupRotation','backupZip','backupSchedule'] as $key) {
            if (empty($this->config[$key])) $missing[] = $key;
        }
        return $missing;
    }

    public function log($msg) {
        file_put_contents($this->logFile, "[".date('Y-m-d H:i:s')."] $msg\n", FILE_APPEND);
    }

    private function isProcessRunning($pid) {
        if (!$pid) return false;
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("tasklist /FI \"PID eq $pid\"", $output);
            return isset($output[1]) && strpos($output[1], (string)$pid) !== false;
        } else {
            return file_exists("/proc/$pid");
        }
    }

    public function isRunning() {
        return file_exists($this->pidFile);
    }

    public function start() {
        if ($this->isRunning()) return false;
        $cmd = "php " . escapeshellarg(__DIR__ . "/run_backup.php") . " > NUL 2>&1 &";
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen("start /MIN " . $cmd, "r"));
        } else {
            exec($cmd);
        }
        $this->log("EVENT:backupStarted");
        return true;
    }

    public function stop() {
        if ($this->isRunning()) {
            $pid = (int)@file_get_contents($this->pidFile);
            if ($pid) {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    exec("taskkill /F /PID $pid");
                } else {
                    exec("kill $pid");
                }
            }
            @unlink($this->pidFile);
        }
        $this->log("EVENT:backupStopped");
        return true;
    }

    public function restart() {
        $this->stop();
        $this->start();
        $this->log("EVENT:backupRestarted");
        return true;
    }

    public function getLog() {
        return @file_get_contents($this->logFile) ?: '';
    }
}