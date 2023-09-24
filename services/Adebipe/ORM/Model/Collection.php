<?php

namespace Adebipe\Model;

/**
 * Collection of models objects
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class Collection implements CollectionInterface
{
    /**
     * The data from the database
     *
     * @var array
     */
    private array $_sql_data = [];

    /**
     * The data that is already converted to objects (lazy loading)
     *
     * @var array
     */
    private array $_data = [];

    /**
     * The name of the object
     */
    private string $_object_name;

    /**
     * The position of the iterator
     */
    private int $_position = 0;

    /**
     * Collection of models objects
     *
     * @param array  $sql_data    The data from the database
     * @param string $object_name The name of the object
     */
    public function __construct(array $sql_data, string $object_name)
    {
        $this->_sql_data = $sql_data;
        $this->_object_name = $object_name;
    }

    /**
     * Find an object by a key
     * Return null if not found
     *
     * @param string $key   The key to search
     * @param string $value The value to search
     *
     * @return object|null
     */
    public function find(string $key, string $value): ?object
    {
        foreach ($this->_sql_data as $data) {
            if ($data[$key] == $value) {
                return new $this->_object_name($data);
            }
        }
        return null;
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset An offset to check for
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->_sql_data[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset The offset to retrieve
     *
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        if (!isset($this->_data[$offset])) {
            $this->_data[$offset] = new $this->_object_name($this->_sql_data[$offset]);
        }
        return $this->_data[$offset];
    }

    /**
     * Offset to set
     *
     * @param mixed $offset The offset to assign the value to
     * @param mixed $value  The value to set
     *
     * @throws \Exception
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        throw new \Exception("You can't add new elements to collection");
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset The offset to unset
     *
     * @throws \Exception
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        throw new \Exception("You can't delete elements from collection");
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return $this->offsetGet($this->_position);
    }

    /**
     * Move forward to next element
     *
     * @return void
     */
    public function next(): void
    {
        $this->_position++;
    }

    /**
     * Return the key of the current element
     *
     * @return int
     */
    public function key(): int
    {
        return $this->_position;
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->_sql_data[$this->_position]);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->_position = 0;
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->_sql_data);
    }
}
