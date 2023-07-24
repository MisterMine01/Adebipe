<?php

namespace Adebipe\Model;

use Adebipe\Services\MsQl;
use ArrayAccess;
use Countable;
use Iterator;

class Collection implements ArrayAccess, Iterator, Countable
{

    private array $sql_data = [];

    private array $data = [];

    private string $object_name;

    private int $position = 0;

    public function __construct(array $sql_data, string $object_name)
    {
        $this->sql_data = $sql_data;
        $this->object_name = $object_name;
    }

    public function find(string $key, string $value): ?object
    {
        foreach ($this->sql_data as $data) {
            if ($data[$key] == $value) {
                return new $this->object_name($data);
            }
        }
        return null;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->sql_data[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        if (!isset($this->data[$offset])) {
            $this->data[$offset] = new $this->object_name($this->sql_data[$offset]);
        }
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        throw new \Exception("You can't add new elements to collection");
    }

    public function offsetUnset($offset): void
    {
        throw new \Exception("You can't delete elements from collection");
    }

    public function current(): mixed
    {
        return $this->offsetGet($this->position);
    }

    public function next(): void
    {
        $this->position++;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->sql_data[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function count(): int
    {
        return count($this->sql_data);
    }
}