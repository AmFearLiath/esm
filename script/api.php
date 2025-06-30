<?php
require_once __DIR__ . '/RoutineManager.php';

header('Content-Type: application/json');
$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
$type = $_POST['type'] ?? $_GET['type'] ?? '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Optionen speichern/laden OHNE RoutineManager
if ($action === 'saveConfig') {
    $data = json_decode($_POST['data'] ?? '', true);
    if (!$data) {
        echo json_encode(['ok'=>false, 'msg'=>'Invalid config data']);
        exit;
    }
    file_put_contents(__DIR__ . '/../config.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo json_encode(['ok'=>true]);
    exit;
}
if ($action === 'loadConfig') {
    $data = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
    echo json_encode(['ok'=>true, 'data'=>$data]);
    exit;
}

// --- Logdateien leeren ---
if ($action === 'clearlog') {
    if ($type === 'backup') {
        $logfile = __DIR__ . '/logs/backup.log';
    } elseif ($type === 'schedule') {
        $logfile = __DIR__ . '/logs/scheduler.log';
    } else {
        echo json_encode(['ok'=>false, 'msg'=>'Unknown log type']);
        exit;
    }
    // Datei leeren
    file_put_contents($logfile, '');
    echo json_encode(['ok'=>true]);
    exit;
}

// Ab hier NUR für Routinen
try {
    $routine = RoutineManager::getInstance($type, $config);

    if ($action === 'validate') {
        $missing = $routine->validateConfig();
        if ($missing) {
            echo json_encode(['ok'=>false, 'missing'=>$missing]);
        } else {
            echo json_encode(['ok'=>true]);
        }
        exit;
    }

    if ($action === 'start') {
        $ok = $routine->start();
        echo json_encode(['ok'=>$ok]);
        exit;
    }
    if ($action === 'stop') {
        $ok = $routine->stop();
        echo json_encode(['ok'=>$ok]);
        exit;
    }
    if ($action === 'restart') {
        $ok = $routine->restart();
        echo json_encode(['ok'=>$ok]);
        exit;
    }
    if ($action === 'status') {
        echo json_encode(['ok'=>true, 'running'=>$routine->isRunning()]);
        exit;
    }
    if ($action === 'log') {
        echo json_encode(['ok'=>true, 'log'=>$routine->getLog()]);
        exit;
    }
    echo json_encode(['ok'=>false, 'msg'=>'Unknown action']);
} catch(Exception $e) {
    echo json_encode(['ok'=>false, 'msg'=>$e->getMessage()]);
}