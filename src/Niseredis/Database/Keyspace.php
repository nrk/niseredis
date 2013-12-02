<?php

/*
 * This file is part of the Niseredis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Niseredis\Database;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Niseredis\Database\Key\KeyInterface;

class Keyspace implements ArrayAccess, Countable, IteratorAggregate
{
    private $keyspace;

    public function __construct()
    {
        $this->keyspace = array();
    }

    public function attach($key, KeyInterface $object)
    {
        $this->keyspace[$key] = $object;
    }

    public function __clone()
    {
        // Yup, this may look dirty but it works and is way faster than any
        // other solution such as iterating the keyspace to clone key objects.
        $this->keyspace = unserialize(serialize($this->keyspace));
    }

    public function detach($key)
    {
        $object = null;

        if (isset($this->keyspace[$key])) {
            $object = $this->keyspace[$key];
            unset($this->keyspace[$key]);
        }

        return $object;
    }

    public function reset()
    {
        $this->keyspace = array();
    }

    public function getRandom()
    {
        if (!$key = array_rand($this->keyspace)) {
            return null;
        }

        return array($key, $this->keyspace[$key]);
    }

    public function offsetSet($key, $object)
    {
        $this->attach($key, $object);
    }

    public function offsetExists($key)
    {
        return isset($this->keyspace[$key]);
    }

    public function offsetUnset($key)
    {
        unset($this->keyspace[$key]);
    }

    public function offsetGet($key)
    {
        if (isset($this->keyspace[$key])) {
            return $this->keyspace[$key];
        }
    }

    public function count()
    {
        return count($this->keyspace);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->keyspace);
    }
}
