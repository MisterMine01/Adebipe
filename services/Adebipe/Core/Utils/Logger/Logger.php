<?php

namespace Adebipe\Services;

use Adebipe\Services\Interfaces\RegisterServiceInterface;
use Adebipe\Services\Interfaces\StarterServiceInterface;

/**
 * Logger of the application
 *
 * @package Adebipe\Services
 */
class Logger implements StarterServiceInterface, RegisterServiceInterface
{
    private SentryInterfaces $sentry;
    public $logTrace = array();
    private $logFile;
    private $loglevel;
    private $loglevels = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4
    ];


    /**
     * Logger of the application
     */
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
        $class = getenv('SENTRY_CLASS');
        if (class_exists($class)) {
            $this->sentry = new $class();
            $this->info($class . ' sentry loaded');
        } else {
            $this->debug("No sentry");
        }

        set_error_handler(
            function ($errno, $errstr, $errfile, $errline) {
                $this->warning($errstr . ' in ' . $errfile . ' on line ' . $errline);
            },
            E_WARNING
        );
    }

    public function atEnd(): void
    {
        $this->info('Stopping Logger');
        restore_error_handler();
        fclose($this->logFile);
    }

    /**
     * Get the log string
     * [Date] (Type) Class : Message
     *
     * @param  string $type
     * @param  string $message
     * @return string
     */
    private function getString(string $type, string $message): string
    {
        $trace = debug_backtrace();
        $call_class = "Main";
        if (isset($trace[3]['class'])) {
            $call_class = $trace[3]['class'];
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
        return $log;
    }

    /**
     * Log a message
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

        $log = $this->getString($type, $message);

        $this->logTrace[] = $log;

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
        $backtrace = debug_backtrace();
        $backtrace[2]["line"] = $backtrace[1]["line"];
        $backtrace[2]["file"] = $backtrace[1]["file"];
        $this->sendSentry(array_splice($backtrace, 2));
    }

    /**
     * Log an error message
     *
     * @param string $message
     */
    public function error(string $message): void
    {
        $this->log('ERROR', $message);
        $backtrace = debug_backtrace();
        $this->sendSentry(array_splice($backtrace, 2));
    }

    /**
     * Log a critical message
     *
     * @param string $message
     */
    public function critical(string $message, ?array $backtrace = null): void
    {
        $this->log('CRITICAL', $message);
        if ($backtrace === null) {
            $backtrace = debug_backtrace();
            $backtrace[2]["line"] = $backtrace[1]["line"];
            $backtrace[2]["file"] = $backtrace[1]["file"];
            $backtrace = array_splice($backtrace, 2);
        }
        $this->sendSentry($backtrace);
    }


    /**
     * Send to Sentry
     */
    public function sendSentry($backtrace): void
    {
        if (!isset($this->sentry)) {
            return;
        }
        $this->sentry->sendSentry($this, $backtrace);
    }
}
