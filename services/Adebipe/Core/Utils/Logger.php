<?php

namespace Adebipe\Services;

use Adebipe\Services\Interfaces\RegisterServiceInterface;
use Adebipe\Services\Interfaces\StarterServiceInterface;

/**
 * Logger of the application
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class Logger implements StarterServiceInterface, RegisterServiceInterface
{
    private static array $_loglevels = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4
    ];

    private ErrorSenderInterface $_sender;
    public array $logTrace = array();


    private mixed $_logFile;
    private int $_loglevel;


    /**
     * Logger of the application
     */
    public function __construct()
    {
        if (!is_dir('logs')) {
            mkdir('logs');
        }
        $log_level = Settings::getConfig("CORE.LOGGER.LOG_LEVEL");
        fwrite(STDOUT, "Log level : " . $log_level . PHP_EOL);
        if ($log_level === false) {
            $log_level = 1;
        }
        $this->_loglevel = intval($log_level);
        if ($this->_loglevel < 0 || $this->_loglevel > 4) {
            throw new \Exception('Invalid log level');
        }
        if (!Settings::getConfig("CORE.LOGGER.LOG_IN_FILE")) {
            $this->_logFile = STDOUT;
        } else {
            $this->_logFile = fopen('logs/' . date('Y-m-d-H-i-s') . '.log', 'wb');
        }
        $this->info('Starting Logger');
    }

    /**
     * Get the log levels
     *
     * @return string
     */
    public function getLogLevels(): string
    {
        return array_keys(self::$_loglevels)[$this->_loglevel];
    }

    /**
     * Function to run at the start of the application
     *
     * @return void
     */
    public function atStart(): void
    {
        $class = Settings::getConfig("CORE.LOGGER.ERROR_CLASS");
        if (!$class) {
            $this->debug("No sentry");
            return;
        }
        if (class_exists($class)) {
            $instance = new $class();
            if (!$instance instanceof ErrorSenderInterface) {
                throw new \Exception('The error sender must implement ErrorSenderInterface');
            }
            $this->_sender = $instance;
            $this->info($class . ' sentry loaded');
        } else {
            $this->debug("No sentry");
        }

        set_error_handler(
            function (int $errno, string $errstr, string $errfile, int $errline): bool {
                $this->warning($errstr . ' in ' . $errfile . ' on line ' . $errline);
                return true;
            },
            E_WARNING
        );
    }

    /**
     * Function to run at the end of the application
     *
     * @return void
     */
    public function atEnd(): void
    {
        $this->info('Stopping Logger');
        restore_error_handler();
        fclose($this->_logFile);
    }

    /**
     * Get the log string
     * [Date] (Type) Class : Message
     *
     * @param string $type    The type of the log
     * @param string $message The message of the log
     *
     * @return string
     */
    private function _getString(string $type, string $message): string
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
     * @param string $type    The type of the log
     * @param string $message The message of the log
     *
     * @return void
     */
    private function _log(string $type, string $message): void
    {
        if (!isset(self::$_loglevels[$type])) {
            throw new \Exception('Invalid log type');
        }
        if ($this->_loglevel > self::$_loglevels[$type]) {
            return;
        }

        $log = $this->_getString($type, $message);

        $this->logTrace[] = $log;

        if (defined('STDOUT')) {
            fwrite(STDOUT, $log);
            fflush(STDOUT);
        }
        $log = mb_convert_encoding($log, 'UTF-8');
        if ($this->_logFile === STDOUT) {
            return;
        }
        fwrite($this->_logFile, $log);
        fflush($this->_logFile);
    }

    /**
     * Log a debug message
     *
     * @param string $message The message of the log
     *
     * @infection-ignore-all
     *
     * @return void
     */
    public function debug(string $message): void
    {
        $this->_log('DEBUG', $message);
    }

    /**
     * Log an info message
     *
     * @param string $message The message of the log
     *
     * @infection-ignore-all
     *
     * @return void
     */
    public function info(string $message): void
    {
        $this->_log('INFO', $message);
    }

    /**
     * Log a warning message
     *
     * @param string $message The message of the log
     *
     * @infection-ignore-all
     *
     * @return void
     */
    public function warning(string $message): void
    {
        $this->_log('WARNING', $message);
        $backtrace = debug_backtrace();
        $backtrace[2]["line"] = $backtrace[1]["line"];
        $backtrace[2]["file"] = $backtrace[1]["file"];
        $this->_sendError(array_splice($backtrace, 2));
    }

    /**
     * Log an error message
     *
     * @param string $message The message of the log
     *
     * @infection-ignore-all
     *
     * @return void
     */
    public function error(string $message): void
    {
        $this->_log('ERROR', $message);
        $backtrace = debug_backtrace();
        $this->_sendError(array_splice($backtrace, 2));
    }

    /**
     * Log a critical message
     *
     * @param string $message   The message of the log
     * @param array  $backtrace The backtrace of the error
     *
     * @infection-ignore-all
     *
     * @return void
     */
    public function critical(string $message, ?array $backtrace = null): void
    {
        $this->_log('CRITICAL', $message);
        if ($backtrace === null) {
            $backtrace = debug_backtrace();
            $backtrace[2]["line"] = $backtrace[1]["line"];
            $backtrace[2]["file"] = $backtrace[1]["file"];
            $backtrace = array_splice($backtrace, 2);
        }
        $this->_sendError($backtrace);
    }


    /**
     * Send the error to the sentry
     *
     * @param array $backtrace The backtrace of the error
     *
     * @return void
     */
    private function _sendError($backtrace): void
    {
        if (!isset($this->_sender)) {
            return;
        }
        $this->_sender->sendError($this, $backtrace);
    }
}
