<?php

namespace Api\Services;

use Api\Services\Interfaces\RegisterServiceInterface;
use Api\Services\Interfaces\ServiceInterface;

class Logger implements ServiceInterface, RegisterServiceInterface
{
    private $logFile;
    private $loglevel;
    private $loglevels = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4
    ];

    public function atStart(): void
    {
        if (!is_dir('logs')) {
            mkdir('logs');
        }
        $this->loglevel = getenv('LOG_LEVEL') ?? 1;
        $this->logFile = fopen('logs/' . date('Y-m-d-H-i-s') . '.log', 'w');
        $this->info('Starting application');
    }

    public function atEnd(): void
    {
        $this->info('Stopping application');
        fclose($this->logFile);
    }

    /**
     * Log a message
     * [Date](Type)Class:Message
     * 
     * @param string $type
     * @param string $message
     */
    private function log(string $type, string $message): void
    {
        if (!isset($this->loglevels[$type])) {
            throw new \Exception('Invalid log type');
        }
        if ($this->loglevel <= $this->loglevels[$type]) {
            return;
        }
        $call_class = debug_backtrace()[1]['class'];
        $call_class = explode('\\', $call_class);
        $call_class = end($call_class);
        $log = '[' . date('Y-m-d H:i:s') . '](' . $type . ')' . $call_class . ':' . $message . PHP_EOL;
        fwrite($this->logFile, $log);
    }

    /**
     * Log a debug message
     * 
     * @param string $message
     */
    public function debug(string $message): void
    {
        $this->log('DEBUG', $message);
    }

    /**
     * Log an info message
     * 
     * @param string $message
     */
    public function info(string $message): void
    {
        $this->log('INFO', $message);
    }
    
    /**
     * Log a warning message
     * 
     * @param string $message
     */
    public function warning(string $message): void
    {
        $this->log('WARNING', $message);
    }

    /**
     * Log an error message
     * 
     * @param string $message
     */
    public function error(string $message): void
    {
        $this->log('ERROR', $message);
    }

    /**
     * Log a critical message
     * 
     * @param string $message
     */
    public function critical(string $message): void
    {
        $this->log('CRITICAL', $message);
    }
}