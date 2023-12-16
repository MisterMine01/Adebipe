<?php

namespace Adebipe\Services;

use Adebipe\Services\Interfaces\RegisterServiceInterface;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Execute queries on a MySQl database
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class MsQl implements RegisterServiceInterface
{
    private ?PDO $_connection;
    private string $_driver;
    private string $_host;
    private string $_database;
    private string $_user;
    private string $_password;
    private bool $_last_query_success;

    /**
     * MsQl constructor.
     *
     * @param Logger $_logger The logger
     */
    public function __construct(
        private Logger $_logger
    ) {
        $connection_string = Settings::getEnvVariable('DB_CONNECTION');
        if (!$connection_string) {
            $this->_logger->warning('DB_CONNECTION environment variable not set');
            return;
        }
        // Extracting connection parameters
        // Format: driver://user:password@host/database
        $ptn_start = 0;
        $ptn_end = strpos($connection_string, '://');
        if ($ptn_end === false) {
            $this->_logger->error("Invalid connection string: " . $connection_string);
            return;
        }
        $this->_driver = substr($connection_string, 0, $ptn_end);
        $ptn_start = $ptn_end + 3;
        $ptn_end = strpos($connection_string, ':', $ptn_start);
        if ($ptn_end === false) {
            $this->_logger->error("Invalid connection string: " . $connection_string);
            return;
        }
        $this->_user = substr($connection_string, $ptn_start, $ptn_end - $ptn_start);
        $ptn_start = $ptn_end + 1;
        $ptn_end = strpos($connection_string, '@', $ptn_start);
        if ($ptn_end === false) {
            $this->_logger->error("Invalid connection string: " . $connection_string);
            return;
        }
        $this->_password = substr($connection_string, $ptn_start, $ptn_end - $ptn_start);
        $ptn_start = $ptn_end + 1;
        $ptn_end = strpos($connection_string, '/', $ptn_start);
        if ($ptn_end === false) {
            $this->_logger->error("Invalid connection string: " . $connection_string);
            return;
        }
        $this->_host = substr($connection_string, $ptn_start, $ptn_end - $ptn_start);
        $ptn_start = $ptn_end + 1;
        $this->_database = substr($connection_string, $ptn_start);
        $connection_string = "$this->_driver:host=$this->_host;dbname=$this->_database";
        $this->_logger->info("Connecting to database: $connection_string");
        $this->_logger->info("User: $this->_user");
        $this->_logger->info("Password: $this->_password");
        try {
            $this->_connection = new PDO($connection_string, $this->_user, $this->_password);
        } catch (PDOException $e) {
            $this->_logger->error("PDO can't be opened");
            $this->_connection = null;
        }
    }

    /**
     * Prepare a query
     *
     * @param string $query The query to prepare
     *
     * @return PDOStatement
     */
    public function prepare(string $query): PDOStatement
    {
        if ($this->_connection === null) {
            throw new \Exception("Connection to database not opened");
        }
        $result = $this->_connection->prepare($query);
        if ($result === false) {
            throw new \Exception("Error preparing query: " . $this->_connection->errorInfo()[2]);
        }
        return $result;
    }

    /**
     * Execute a query and return the result as an array
     *
     * @param PDOStatement $statement The statement to execute
     * @param array|null   $params    The parameters to bind
     * @param array|null   $types     The types of the parameters
     *
     * @return array
     */
    public function execute(PDOStatement $statement, array|null $params = null, array|null $types = null): array
    {
        $data = null;
        if ($params !== null && $types !== null) {
            $this->_logger->info("Binding params");
            $this->_logger->info("Params: " . json_encode($params));
            $this->_logger->info("Types: " . json_encode($types));
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
            if (Settings::getEnvVariable('ENV') === 'dev') {
                $statement->debugDumpParams();
            }
            $this->_logger->critical("Error executing query: " . $e->getMessage());
            exit(1);
        }
        if ($data === false) {
            $this->_logger->error("Error executing query: " . $statement->errorInfo()[2]);
            $this->_last_query_success = false;
            return [];
        }
        $this->_logger->info("Query executed successfully");
        $this->_logger->info("Query: " . $statement->queryString);
        $this->_last_query_success = true;
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Test if the last query was successful
     *
     * @return bool
     */
    public function getLastQuerySuccess(): bool
    {
        return $this->_last_query_success;
    }

    /**
     * Get the last insert id
     *
     * @return int
     */
    public function getLastInsertId(): int
    {
        $result = $this->_connection->lastInsertId();
        if ($result === false) {
            $this->_logger->error("Error getting last insert id");
            return 0;
        }
        return (int)$result;
    }

    /**
     * Get the list of tables in the database
     *
     * @return array
     */
    public function getTable(): array
    {
        $query = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE' AND TABLE_CATALOG=?";
        $statement = $this->prepare($query);
        $data = $this->execute($statement, [$this->_database]);
        return $data;
    }
}
