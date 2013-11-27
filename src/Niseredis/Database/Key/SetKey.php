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

class SetKey implements KeyInterface
{
    protected $members;
    protected $expiration = null;

    public function __construct()
    {
        $this->members = array();
    }

    public function getType()
    {
        return 'set';
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

    /**
     * @link http://redis.io/commands/sadd
     */
    public function sadd(array $members)
    {
        $added = 0;

        foreach ($members as $member) {
            $member = (string) $member;

            if (!isset($this->members[$member])) {
                $this->members[$member] = true;
                $added++;
            }
        }

        return $added;
    }

    /**
     * @link http://redis.io/commands/scard
     */
    public function scard()
    {
        return count($this->members);
    }

    /**
     * @link http://redis.io/commands/sismember
     */
    public function sismember($member)
    {
        return isset($this->members[(string) $member]);
    }

    /**
     * @link http://redis.io/commands/smembers
     */
    public function smembers()
    {
        return array_keys($this->members);
    }

    /**
     * @link http://redis.io/commands/srem
     */
    public function srem(array $members)
    {
        $removed = 0;

        foreach ($members as $member) {
            $member = (string) $member;

            if (isset($this->members[$member])) {
                unset($this->members[$member]);
                $removed++;
            }
        }

        return $removed;
    }
}
