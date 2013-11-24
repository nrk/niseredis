<?php

/*
 * This file is part of the Niseredis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Niseredis\Database\Key;

class HashKey implements KeyInterface
{
    protected $dictionary;

    public function __construct()
    {
        $this->dictionary = array();
    }

    public function getType()
    {
        return 'hash';
    }

    public function getValue()
    {
        return $this->dictionary;
    }

    public function isEmpty()
    {
        return empty($this->dictionary);
    }

    /**
     * @link http://redis.io/commands/hget
     */
    public function hget($field)
    {
        if (isset($this->dictionary[$field])) {
            return $this->dictionary[$field];
        }
    }

    /**
     * @link http://redis.io/commands/hgetall
     */
    public function hgetall()
    {
        return $this->dictionary;
    }

    /**
     * @link http://redis.io/commands/hset
     */
    public function hset($field, $value)
    {
        $created = isset($this->dictionary[$field]);
        $this->dictionary[$field] = (string) $value;

        return (int) $created;
    }
}
