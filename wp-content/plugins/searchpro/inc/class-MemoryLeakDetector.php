<?php

class MemoryLeakDetector
{
    private $checkpoints = [];
    private $threshold;
    private $logFile;

    public function __construct($threshold = 1024 * 1024, $logFile = null) // Default threshold is 1MB
    {
        $this->threshold = $threshold;
        $this->logFile = $logFile ?: optifer_PATH . 'memory_leaks.log';
    }

    public function addCheckpoint($name)
    {
        $this->checkpoints[$name] = memory_get_usage();
    }

    public function detectLeaks()
    {
        $previousUsage = null;
        $leaks = [];

        foreach ($this->checkpoints as $name => $usage) {
            if ($previousUsage !== null && ($usage - $previousUsage) > $this->threshold) {
                $leaks[] = [
                    'checkpoint' => $name,
                    'increase' => $usage - $previousUsage
                ];
            }
            $previousUsage = $usage;
        }

        return $leaks;
    }

    public function reportLeaks()
    {
        $leaks = $this->detectLeaks();

        if (empty($leaks)) {
            $this->log("No memory leaks detected.\n");
        } else {
            $this->log("Potential memory leaks detected:\n");
            foreach ($leaks as $leak) {
                $this->log("Checkpoint: {$leak['checkpoint']}, Memory increase: {$leak['increase']} bytes\n");
            }
        }
    }

    private function log($message)
    {
        file_put_contents($this->logFile, $message, FILE_APPEND);
    }
}


global $mem_detector;
$mem_detector = new MemoryLeakDetector(1.5 * 1024 * 1024); // 1.5MB threshold