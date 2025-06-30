<?php
require_once __DIR__ . '/Backuper.php';
require_once __DIR__ . '/Scheduler.php';

class RoutineManager {
    public static function getInstance($type, $config) {
        if ($type === 'backup') return new Backuper($config);
        if ($type === 'schedule') return new Scheduler($config);
        throw new Exception("Unknown routine type: $type");
    }
}