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

class ListKey implements KeyInterface
{
    protected $list;

    public function __construct()
    {
        $this->list = array();
    }

    public function getType()
    {
        return 'list';
    }

    public function getValue()
    {
        return $this->list;
    }

    public function isEmpty()
    {
        return empty($this->list);
    }

    /**
     * @link http://redis.io/commands/lindex
     */
    public function lindex($index)
    {
        $llen = $this->llen();

        if ($index < 0) {
            $index = $llen + $index;
        }

        if ($index >= 0 && $index < $llen) {
            return $this->list[$index];
        }

        return null;
    }

    /**
     * @link http://redis.io/commands/llen
     */
    public function llen()
    {
        return count($this->list);
    }

    /**
     * @link http://redis.io/commands/lpop
     */
    public function lpop()
    {
        return array_shift($this->list);
    }

    /**
     * @link http://redis.io/commands/lpush
     */
    public function lpush(array $values)
    {
        foreach ($values as $value) {
            array_unshift($this->list, (string) $value);
        }

        return $this->llen();
    }

    /**
     * @link http://redis.io/commands/lrange
     */
    public function lrange($start, $stop)
    {
        if ($start < 0) {
            $start = 0;
        }

        $count = $stop >= 0 ? $start + $stop : $this->llen() + $stop;

        if ($count >= 0) {
            return array_slice($this->list, $start, $count + 1);
        }

        return array();
    }

    /**
     * @link http://redis.io/commands/rpop
     */
    public function rpop()
    {
        return array_pop($this->list);
    }

    /**
     * @link http://redis.io/commands/rpush
     */
    public function rpush(array $values)
    {
        foreach ($values as $value) {
            array_push($this->list, (string) $value);
        }

        return $this->llen();
    }
}