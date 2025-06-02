<?php

namespace Raiaccept\RaiacceptApiClient;

class WpFileLogger
{
    /**
     * @var bool
     */
    private $shouldLog;

    public function __construct($shouldLog)
    {
        $this->shouldLog = $shouldLog;
    }

    /**
     * @param $message
     *
     * @return void
     */
    public function info($message)
    {
        $this->log($message, 'INFO');
    }

    /**
     * @param $message
     * @param $level
     *
     * @return void
     */
    public function log($message, $level = 'info')
    {
        if (!$this->shouldLog) {
            return;
        }
        $date = gmdate('Y-m-d H:i:s');
        $formattedMessage = "[$date] [$level] $message" . PHP_EOL;
        wc_get_logger()->log($level, $formattedMessage);
    }

    /**
     * @param $message
     *
     * @return void
     */
    public function warning($message)
    {
        $this->log($message, 'warning');
    }

    /**
     * @param $message
     *
     * @return void
     */
    public function error($message)
    {
        $this->log($message, 'error');
    }
}
