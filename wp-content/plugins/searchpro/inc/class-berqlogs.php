<?php
class berqLogs {
    private $logFile;

    public function __construct() {
        $this->logFile = optifer_cache . 'berqwp.log';
    }

    public function log($message, $status = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp][$status]: $message" . PHP_EOL;
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    public function info($message) {
        $this->log($message, 'INFO');
    }

    public function warning($message) {
        $this->log($message, 'WARNING');
    }

    public function error($message) {
        $this->log($message, 'ERROR');
    }
}

global $berq_log;
$berq_log = new berqLogs();