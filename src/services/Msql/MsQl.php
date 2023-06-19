<?php

namespace Api\Services;

use Api\Services\Interfaces\RegisterServiceInterface;
use PDO;
use PDOStatement;

class MsQl implements RegisterServiceInterface
{
    private Logger $logger;
    private PDO $connection;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $connection_string = getenv('DB_CONNECTION');
        if (!$connection_string) {
            $this->logger->warning('DB_CONNECTION environment variable not set');
            return;
        }
        $ptn_start = 0;
        $ptn_end = strpos($connection_string, '://');
        $drive = substr($connection_string, 0, $ptn_end);
        $ptn_start = $ptn_end + 3;
        $ptn_end = strpos($connection_string, ':', $ptn_start);
        $user = substr($connection_string, $ptn_start, $ptn_end - $ptn_start);
        $ptn_start = $ptn_end + 1;
        $ptn_end = strpos($connection_string, '@', $ptn_start);
        $password = substr($connection_string, $ptn_start, $ptn_end - $ptn_start);
        $ptn_start = $ptn_end + 1;
        $ptn_end = strpos($connection_string, '/', $ptn_start);
        $host = substr($connection_string, $ptn_start, $ptn_end - $ptn_start);
        $ptn_start = $ptn_end + 1;
        $database = substr($connection_string, $ptn_start);
        $connection_string = "$drive:host=$host;dbname=$database";
        $this->logger->info("Connecting to database: $connection_string");
        $this->logger->info("User: $user");
        $this->logger->info("Password: $password");
        $this->connection = new PDO($connection_string, $user, $password);
    }

    public function prepare(string $query): PDOStatement|false
    {
        return $this->connection->prepare($query);
    }

    public function execute(PDOStatement $statement, array|null $params = null): array
    {
        $data = $statement->execute($params);
        if ($data === false) {
            $this->logger->error("Error executing query: " . $statement->errorInfo()[2]);
            return [];
        }
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

}