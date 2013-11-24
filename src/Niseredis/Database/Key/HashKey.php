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
     * @link http://redis.io/commands/hdel
     */
    public function hdel(array $fields)
    {
        $deleted = 0;

        foreach ($fields as $field) {
            if (isset($this->dictionary[$field])) {
                unset($this->dictionary[$field]);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * @link http://redis.io/commands/hexists
     */
    public function hexists($field)
    {
        return (int) isset($this->dictionary[$field]);
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
     * @link http://redis.io/commands/hkeys
     */
    public function hkeys()
    {
        return array_keys($this->dictionary);
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
