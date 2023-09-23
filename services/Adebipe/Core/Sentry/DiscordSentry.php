<?php

namespace Adebipe\Services;

class DiscordSentry implements SentryInterfaces
{
    private $indexAlreadySent = 0;
    private string $webhook_url;
    private ?string $username;
    private ?string $avatar_url;


    public function __construct()
    {
        $this->webhook_url = getenv('DISCORD_WEBHOOK');
        if (!$this->webhook_url) {
            throw new \Exception("No webhook url");
        }
        $this->username = getenv('DISCORD_USERNAME');
        if (!$this->username) {
            $this->username = null;
        }
        $this->avatar_url = getenv('DISCORD_AVATAR_URL');
        if (!$this->avatar_url) {
            $this->avatar_url = null;
        }
    }

    public function sendSentry(Logger $logger, array $backtrace): void
    {
        if (getenv('ENV') === 'dev') {
            return;
        }
        $logger->info('Sending to Discord');

        $logData = $logger->logTrace;

        $all_sending = [];

        $firstPart = "```";
        foreach (array_slice($logData, $this->indexAlreadySent) as $log) {
            if (strlen($firstPart) + strlen($log) > 1800 - 3) {
                $firstPart .= "```";
                $all_sending[] = $firstPart;
                $firstPart = "```";
            }
            $firstPart .= $log;
        }
        $this->indexAlreadySent = count($logData);
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

        var_dump($all_sending);
        foreach ($all_sending as $message) {
            $this->sendMessage($message);
        }
    }


    private function sendMessage(string $message)
    {
        $data = [
            "content" => $message,
        ];
        if ($this->username) {
            $data["username"] = $this->username;
        }
        if ($this->avatar_url) {
            $data["avatar_url"] = $this->avatar_url;
        }

        $json_data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);


        $userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.109 Safari/537.36';
        $contentType = "Content-type: application/json\r\n" . "Accept-language: en\r\n";

        $result = file_get_contents(
            $this->webhook_url,
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
