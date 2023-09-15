<?php

namespace Adebipe\Services;

use Adebipe\Services\Interfaces\RegisterServiceInterface;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Execute queries on a MySQl database
 * @package Adebipe\Services
 */
class MsQl implements RegisterServiceInterface
{
    private Logger $logger;
    private PDO $connection;
    private string $driver;
    private string $host;
    private string $database;
    private string $user;
    private string $password;
    private bool $last_query_success;

    /**
     * Execute queries on a MySQl database
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $connection_string = getenv('DB_CONNECTION');
        if (!$connection_string) {
            $this->logger->warning('DB_CONNECTION environment variable not set');
            return;
        }
        // Extracting connection parameters
        // Format: driver://user:password@host/database
        $ptn_start = 0;
        $ptn_end = strpos($connection_string, '://');
        $this->driver = substr($connection_string, 0, $ptn_end);
        $ptn_start = $ptn_end + 3;
        $ptn_end = strpos($connection_string, ':', $ptn_start);
        $this->user = substr($connection_string, $ptn_start, $ptn_end - $ptn_start);
        $ptn_start = $ptn_end + 1;
        $ptn_end = strpos($connection_string, '@', $ptn_start);
        $this->password = substr($connection_string, $ptn_start, $ptn_end - $ptn_start);
        $ptn_start = $ptn_end + 1;
        $ptn_end = strpos($connection_string, '/', $ptn_start);
        $this->host = substr($connection_string, $ptn_start, $ptn_end - $ptn_start);
        $ptn_start = $ptn_end + 1;
        $this->database = substr($connection_string, $ptn_start);
        $connection_string = "$this->driver:host=$this->host;dbname=$this->database";
        $this->logger->info("Connecting to database: $connection_string");
        $this->logger->info("User: $this->user");
        $this->logger->info("Password: $this->password");
        try {
            $this->connection = new PDO($connection_string, $this->user, $this->password);
        } catch (PDOException $e) {
            $logger->critical("PDO can't be opened");
        }
    }

    /**
     * Prepare a query
     * @param string $query
     * @return PDOStatement|false
     */
    public function prepare(string $query): PDOStatement|false
    {
        return $this->connection->prepare($query);
    }

    /**
     * Execute a query and return the result as an array
     * @param PDOStatement $statement
     * @param array|null $params
     * @param array|null $types
     * @return array
     */
    public function execute(PDOStatement $statement, array|null $params = null, array|null $types = null): array
    {
        $data = null;
        if ($params !== null && $types !== null) {
            $this->logger->info("Binding params");
            $this->logger->info("Params: " . json_encode($params));
            $this->logger->info("Types: " . json_encode($types));
            $i = 0;
            foreach ($params as $param) {
                $statement->bindValue($i + 1, $param, $types[$i]);
                $i++;
            }
        } else {
            if ($params !== null) {
                $i = 0;
                foreach ($params as $param) {
                    $statement->bindValue($i + 1, $param);
                    $i++;
                }
            }
        }
        try {
            $data = $statement->execute();
        } catch (PDOException $e) {
            if (getenv('ENV') === 'dev') {
                $statement->debugDumpParams();
            }
            $this->logger->critical("Error executing query: " . $e->getMessage());
            exit(1);
        }
        if ($data === false) {
            $this->logger->error("Error executing query: " . $statement->errorInfo()[2]);
            $this->last_query_success = false;
            return [];
        }
        $this->logger->info("Query executed successfully");
        $this->logger->info("Query: " . $statement->queryString);
        $this->last_query_success = true;
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Test if the last query was successful
     * @return bool
     */
    public function get_last_query_success(): bool
    {
        return $this->last_query_success;
    }

    /**
     * Get the list of tables in the database
     */
    public function get_table() {
        $query = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE' AND TABLE_CATALOG=?";
        $statement = $this->prepare($query);
        $data = $this->execute($statement, [$this->database]);
        return $data;
    }
}