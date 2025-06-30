<?php
class Scheduler {
    private $config;
    private $logFile;
    private $pidFile;

    public function __construct($config) {
        $this->config = $config;
        $this->logFile = __DIR__ . '/logs/scheduler.log';
        $this->pidFile = __DIR__ . '/pids/scheduler.pid';

        // Ensure required directories exist
        if (!is_dir(dirname($this->logFile))) {
            @mkdir(dirname($this->logFile), 0777, true);
        }
        if (!is_dir(dirname($this->pidFile))) {
            @mkdir(dirname($this->pidFile), 0777, true);
        }
    }

    public function validateConfig() {
        $missing = [];
        foreach (['ftpServer','ftpPort','ftpUser','ftpPass','ftpDir','scheduleSavegame','scheduleSchedule'] as $key) {
            if (empty($this->config[$key])) $missing[] = $key;
        }
        return $missing;
    }

    public function log($msg) {
        file_put_contents($this->logFile, "[".date('Y-m-d H:i:s')."] $msg\n", FILE_APPEND);
    }

    public function isRunning() {
        return file_exists($this->pidFile);
    }

    public function start() {
        if ($this->isRunning()) return false;
        $cmd = "php " . escapeshellarg(__DIR__ . "/run_scheduler.php") . " > NUL 2>&1 &";
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen("start /MIN " . $cmd, "r"));
        } else {
            exec($cmd);
        }
        $this->log("EVENT:schedulerStarted");
        return true;
    }

    public function stop() {
        if ($this->isRunning()) unlink($this->pidFile);
        $this->log("EVENT:schedulerStopped");
        return true;
    }

    public function restart() {
        $this->stop();
        $this->start();
        $this->log("EVENT:schedulerRestarted");
        return true;
    }

    public function getLog() {
        return @file_get_contents($this->logFile) ?: '';
    }
}