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

use UnexpectedValueException;

class HashKey implements KeyInterface
{
    protected $dictionary;
    protected $expiration = null;

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

    public function getExpiration()
    {
        return $this->expiration;
    }

    public function setExpiration($time)
    {
        $this->expiration = $time;
    }

    public function isExpired()
    {
        if (!isset($this->expiration)) {
            return false;
        }

        return $this->expiration - microtime(true) <= 0;
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
     * @link http://redis.io/commands/hincrby
     */
    public function hincrby($field, $increment)
    {
        if ("$increment" != (int) $increment) {
            throw new UnexpectedValueException("value is not an integer or out of range");
        }

        $value = $this->hget($field) ?: 0;

        if (!is_numeric($value) ||
            stripos($value, '.') !== false ||
            stripos($value, 'e') !== false
        ) {
            throw new UnexpectedValueException("value is not an integer or out of range");
        }

        $this->hset($field, (string) $value += $increment);

        return (string) $value;
    }

    /**
     * @link http://redis.io/commands/hincrbyfloat
     */
    public function hincrbyfloat($field, $increment)
    {
        $value = $this->hget($field) ?: 0;

        if ("$increment" != (float) $increment || !is_numeric($value)) {
            throw new UnexpectedValueException("value is not an integer or out of range");
        }

        $this->hset($field, (string) $value += $increment);

        return (string) $value;
    }

    /**
     * @link http://redis.io/commands/hkeys
     */
    public function hkeys()
    {
        return array_keys($this->dictionary);
    }

    /**
     * @link http://redis.io/commands/hlen
     */
    public function hlen()
    {
        return count($this->dictionary);
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

    /**
     * @link http://redis.io/commands/hvals
     */
    public function hvals()
    {
        return array_values($this->dictionary);
    }
}
