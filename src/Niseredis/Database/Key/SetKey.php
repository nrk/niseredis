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

    protected function math($fn, $others)
    {
        if (!$others) {
            return $this->smembers();
        }

        return call_user_func_array($fn, array_map(function ($dbkey) {
            return $dbkey ? $dbkey->smembers() : array();
        }, array_merge(array($this), $others)));
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
     * @link http://redis.io/commands/sdiff
     */
    public function sdiff(array $others)
    {
        return array_values($this->math('array_diff', $others));
    }

    /**
     * @link http://redis.io/commands/sinter
     */
    public function sinter(array $others)
    {
        return array_values($this->math('array_intersect', $others));
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
     * @link http://redis.io/commands/spop
     */
    public function spop()
    {
        $member = array_rand($this->members);
        unset($this->members[$member]);

        return $member;
    }

    /**
     * @link http://redis.io/commands/srandmember
     */
    public function srandmember($count = 1)
    {
        $unique = $count > 0;
        $count = abs($count);
        $members = array();

        while ($count) {
            $member = array_rand($this->members);

            if ($unique && in_array($member, $members)) {
                continue;
            }

            $members[] = $member;
            $count--;
        }

        return $members;
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

    /**
     * @link http://redis.io/commands/sunion
     */
    public function sunion(array $others)
    {
        return array_values(array_unique($this->math('array_merge', $others)));
    }
}
