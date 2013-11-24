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

class SortedSetKey implements KeyInterface
{
    protected $members;
    protected $expiration = null;

    public function __construct()
    {
        $this->members = array();
    }

    public function getType()
    {
        return 'zset';
    }

    public function getValue()
    {
        return $this->members;
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
        return empty($this->members);
    }
}
