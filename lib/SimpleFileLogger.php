<?php

namespace Raiaccept\RaiacceptApiClient;

class SimpleFileLogger
{
    private $logFile;

    public function __construct($filePath)
    {
        $this->logFile = $filePath;
        if (!is_null($this->logFile)) {
            $this->initializeLogFile();
        }
    }

    private function initializeLogFile()
    {
        if (!file_exists($this->logFile)) {
            $directory = dirname($this->logFile);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            file_put_contents($this->logFile, ''); // Create empty file
        }
    }

    public function info($message)
    {
        $this->log($message, 'INFO');
    }

    public function log($message, $level = 'INFO')
    {
        if (is_null($this->logFile)) {
            return;
        }
        $date = gmdate('Y-m-d H:i:s');
        $formattedMessage = "[$date] [$level] $message" . PHP_EOL;
        file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);
    }

    public function warning($message)
    {
        $this->log($message, 'WARNING');
    }

    public function error($message)
    {
        $this->log($message, 'ERROR');
    }
}
