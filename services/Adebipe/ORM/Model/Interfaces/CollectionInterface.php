<?php

namespace Adebipe\Model;

use ArrayAccess;
use Countable;
use Iterator;

interface CollectionInterface extends ArrayAccess, Iterator, Countable
{
    /**
     * Find an object in the collection
     *
     * @param  string $key   The key to search
     * @param  string $value The value to search
     * @return object|null
     */
    function find(string $key, string $value): ?object;
}
