<?php

namespace Api\Services;

use Api\Services\Interfaces\RegisterServiceInterface;
use Api\Services\Interfaces\StarterServiceInterface;

class Logger implements StarterServiceInterface, RegisterServiceInterface
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


    public function __construct()
    {
        if (!is_dir('logs')) {
            mkdir('logs');
        }
        $this->loglevel = getenv('LOG_LEVEL') ?? 1;
        $this->logFile = fopen('logs/' . date('Y-m-d-H-i-s') . '.log', 'wb');
        $this->info('Starting Logger');
    }

    public function atStart(): void
    {
    }

    public function atEnd(): void
    {
        $this->info('Stopping Logger');
        fclose($this->logFile);
    }

    /**
     * Log a message
     * [Date] (Type) Class : Message
     * 
     * @param string $type
     * @param string $message
     */
    private function log(string $type, string $message): void
    {
        if (!isset($this->loglevels[$type])) {
            throw new \Exception('Invalid log type');
        }
        if ($this->loglevel > $this->loglevels[$type]) {
            return;
        }
        $trace = debug_backtrace();
        $call_class = "Main";
        if (isset($trace[2]['class'])) {
            $call_class = $trace[2]['class'];
        }
        $call_class = explode('\\', $call_class);
        $call_class = end($call_class);
        $log_format = "[%s] (%' 7s) %' 15s : ";
        $log = vsprintf($log_format, array(date('Y-m-d H:i:s'), $type, $call_class));
        if (strpos($message, PHP_EOL) !== false) {
            $message_line = explode(PHP_EOL, $message);
            $message = "";
            foreach ($message_line as $line) {
                $message .= $line . PHP_EOL . str_repeat(' ', strlen($log));
            }
        }
        $log .= $message . PHP_EOL;
        

        if (defined('STDOUT')) {
            fwrite(STDOUT, $log);
            fflush(STDOUT);
        }
        $log = mb_convert_encoding($log, 'UTF-8');
        fwrite($this->logFile, $log);
        fflush($this->logFile);
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
