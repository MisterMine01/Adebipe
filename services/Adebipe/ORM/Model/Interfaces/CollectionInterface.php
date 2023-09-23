<?php

namespace Adebipe\Model;

use ArrayAccess;
use Countable;
use Iterator;

/**
 * Collection of Model objects (lazy loading)
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
interface CollectionInterface extends ArrayAccess, Iterator, Countable
{
    /**
     * Find an object in the collection
     *
     * @param string $key   The key to search
     * @param string $value The value to search
     *
     * @return object|null
     */
    public function find(string $key, string $value): ?object;
}
