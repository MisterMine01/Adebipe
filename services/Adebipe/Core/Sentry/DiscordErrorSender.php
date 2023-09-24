<?php

namespace Adebipe\Services;

/**
 * Discord error sender
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class DiscordErrorSender implements ErrorSenderInterface
{
    private $_indexAlreadySent = 0;
    private string $_webhook_url;
    private ?string $_username;
    private ?string $_avatar_url;


    /**
     * Constructor of the DiscordErrorSender
     */
    public function __construct()
    {
        $this->_webhook_url = getenv('DISCORD_WEBHOOK');
        if (!$this->_webhook_url) {
            throw new \Exception("No webhook url");
        }
        $this->_username = getenv('DISCORD_USERNAME');
        if (!$this->_username) {
            $this->_username = null;
        }
        $this->_avatar_url = getenv('DISCORD_AVATAR_URL');
        if (!$this->_avatar_url) {
            $this->_avatar_url = null;
        }
    }

    /**
     * Send an error to discord
     *
     * @param Logger $logger    The logger to use
     * @param array  $backtrace The backtrace of the error
     *
     * @return void
     */
    public function sendError(Logger $logger, array $backtrace): void
    {
        if (getenv('ENV') === 'dev') {
            return;
        }
        $logger->info('Sending to Discord');

        $logData = $logger->logTrace;

        $all_sending = [];

        $firstPart = "```";
        foreach (array_slice($logData, $this->_indexAlreadySent) as $log) {
            if (strlen($firstPart) + strlen($log) > 1800 - 3) {
                $firstPart .= "```";
                $all_sending[] = $firstPart;
                $firstPart = "```";
            }
            $firstPart .= $log;
        }
        $this->_indexAlreadySent = count($logData);
        $firstPart .= "```";
        $all_sending[] = $firstPart;

        $secondPart = PHP_EOL . " Backtrace: " . PHP_EOL . "```";
        foreach ($backtrace as $trace) {
            if (!isset($trace["class"])) {
                $trace["class"] = "???";
            }
            if (!isset($trace["function"])) {
                $trace["function"] = "???";
            }
            if (!isset($trace["line"])) {
                $trace["line"] = "???";
            }
            if (!isset($trace["file"])) {
                $trace["file"] = "???";
            }
            $line = ($trace["class"] ?? "") . "::" . $trace["function"] . "()" . PHP_EOL;
            $line .= "   line " . $trace["line"] . " in " . $trace["file"] . PHP_EOL . PHP_EOL;
            if (strlen($secondPart) + strlen($line) > 1800 - 3) {
                $secondPart .= "```";
                $all_sending[] = $secondPart;
                $secondPart = "```";
            }
            $secondPart .= $line;
        }

        $secondPart .= "```";
        $all_sending[] = $secondPart;

        foreach ($all_sending as $message) {
            $this->_sendMessage($message);
        }
    }

    /**
     * Send a message to the discord webhook
     *
     * @param string $message The message to send
     *
     * @return void
     */
    private function _sendMessage(string $message): void
    {
        $data = [
            "content" => $message,
        ];
        if ($this->_username) {
            $data["username"] = $this->_username;
        }
        if ($this->_avatar_url) {
            $data["avatar_url"] = $this->_avatar_url;
        }

        $json_data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);


        $userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko)' .
            'Chrome/48.0.2564.109 Safari/537.36';
        $contentType = "Content-type: application/json\r\n" . "Accept-language: en\r\n";

        $result = file_get_contents(
            $this->_webhook_url,
            false,
            stream_context_create(
                [
                    'http' => [
                        'method' => 'POST',
                        'user_agent' => $userAgent,
                        'header' => $contentType,
                        'content' => $json_data
                    ]
                ]
            )
        );
        if ($result === false) {
            var_dump($json_data);
        }
    }
}
