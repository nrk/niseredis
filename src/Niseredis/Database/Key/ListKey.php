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

use InvalidArgumentException;

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
     * @link http://redis.io/commands/linsert
     */
    public function linsert($where, $pivot, $value)
    {
        $where = strtoupper($where);

        if ($where !== 'BEFORE' && $where !== 'AFTER') {
            throw new InvalidArgumentException("syntax error");
        }

        if (false === $index = array_search($pivot, $this->list, true)) {
            return -1;
        }

        $position = $where === 'BEFORE' ? $index : $index + 1;
        array_splice($this->list, $position, 0, $value);

        return $this->llen();
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
     * @link http://redis.io/commands/lrem
     */
    public function lrem($count, $value)
    {
        if ($count == 0) {
            $before = $this->llen();
            $this->list = array_diff($this->list, array((string) $value));

            return $before - $this->llen();
        }

        $left = $remove = abs($count);
        $last = $this->llen() - 1;
        $index = $count > 0 ? 0 : $last;

        while (0 < $left) {
            if ($value === $this->list[$index]) {
                unset($this->list[$index]);
                $left--;
            }

            $count > 0 ? $index++ : $index--;

            if ($index < 0 || $index > $last) {
                break;
            }
        }

        if ($left === $remove) {
            return 0;
        }

        $this->list = array_values($this->list);
        $removed = $remove - $left;

        return $removed;
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
