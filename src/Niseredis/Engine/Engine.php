<?php

/*
 * This file is part of the Niseredis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Niseredis\Engine;

use InvalidArgumentException;
use Niseredis\Database\Database;

class Engine
{
    public function __construct(Database $database)
    {
        $this->setDatabase($database);
    }

    public function setDatabase(Database $database)
    {
        if (!$database) {
            throw new InvalidArgumentException("Database cannot be NULL");
        }

        $this->database = $database;
    }

    public function getDatabase()
    {
        return $this->database;
    }

    // Strings

    /**
     * @link http://redis.io/commands/append
     */
    public function append($key, $value)
    {
        if ($dbkey = $this->database->getString($key)) {
            $dbkey->append($value);
        } else {
            $dbkey = $this->database->createString($key, $value);
        }

        return $dbkey->strlen();
    }

    /**
     * @link http://redis.io/commands/bitcount
     */
    public function bitcount($key, $start = 0, $end = -1)
    {
        if ($dbkey = $this->database->getString($key)) {
            return $dbkey->bitcount($start, $end);
        }

        return 0;
    }

    /**
     * @link http://redis.io/commands/incrby
     */
    public function incrby($key, $increment)
    {
        if (!$dbkey = $this->database->getString($key)) {
            $dbkey = $this->database->createString($key, '0');
        }

        return $dbkey->incrby($increment);
    }

    /**
     * @link http://redis.io/commands/incrbyfloat
     */
    public function incrbyfloat($key, $increment)
    {
        if (!$dbkey = $this->database->getString($key)) {
            $dbkey = $this->database->createString($key, '0');
        }

        return $dbkey->incrbyfloat($increment);
    }

    /**
     * @link http://redis.io/commands/get
     */
    public function get($key)
    {
        if ($dbkey = $this->database->getString($key)) {
            return $dbkey->getValue();
        }
    }

    /**
     * @link http://redis.io/commands/getbit
     */
    public function getbit($key, $offset)
    {
        if ($dbkey = $this->database->getString($key)) {
            return $dbkey->getbit($offset);
        }

        return 0;
    }

    /**
     * @link http://redis.io/commands/getrange
     */
    public function getrange($key, $start, $end)
    {
        if ($dbkey = $this->database->getString($key)) {
            return $dbkey->getrange($start, $end);
        }

        return '';
    }

    /**
     * @link http://redis.io/commands/getset
     */
    public function getset($key, $value)
    {
        if (!$dbkey = $this->database->getString($key)) {
            $dbkey = $this->database->createString($key, $value);
        }

        return $dbkey->getValue();
    }

    /**
     * @link http://redis.io/commands/mget
     */
    public function mget(array $keys)
    {
        $values = array();

        foreach ($keys as $key) {
            $values[] = $this->get($key);
        }

        return $values;
    }

    /**
     * @link http://redis.io/commands/mset
     */
    public function mset(array $keyvalues)
    {
        foreach ($keyvalues as $key => $value) {
            $this->set($key, $value);
        }

        return true;
    }

    /**
     * @link http://redis.io/commands/msetnx
     */
    public function msetnx(array $keyvalues)
    {
        $database = $this->getDatabase();

        foreach (array_keys($keyvalues) as $key) {
            if ($database->exists($key)) {
                return 0;
            }
        }

        foreach ($keyvalues as $key => $value) {
            $this->set($key, $value);
        }

        return 1;
    }

    /**
     * @link http://redis.io/commands/set
     */
    public function set($key, $value)
    {
        $this->database->createString($key, $value);

        return true;
    }

    /**
     * @link http://redis.io/commands/setbit
     */
    public function setbit($key, $offset, $value)
    {
        $dbkey = $this->database->getString($key, true);
        $oldbit = $dbkey->setbit($offset, $value);

        return $oldbit;
    }

    /**
     * @link http://redis.io/commands/setnx
     */
    public function setnx($key, $value)
    {
        if (!$this->database->exists($key)) {
            $this->set($key, $value);

            return 1;
        }

        return 0;
    }

    /**
     * @link http://redis.io/commands/setrange
     */
    public function setrange($key, $offset, $value)
    {
        if (!$dbkey = $this->database->getString($key)) {
            $dbkey = $this->database->createString($key);
        }

        return $dbkey->setrange($offset, $value);
    }

    /**
     * @link http://redis.io/commands/strlen
     */
    public function strlen($key)
    {
        if ($dbkey = $this->database->getString($key)) {
            return $dbkey->strlen();
        }

        return 0;
    }

    // Lists

    /**
     * @link http://redis.io/commands/lindex
     */
    public function lindex($key, $index)
    {
        if ($dbkey = $this->database->getList($key)) {
            return $dbkey->lindex($index);
        }
    }

    /**
     * @link http://redis.io/commands/linsert
     */
    public function linsert($key, $where, $pivot, $value)
    {
        if ($dbkey = $this->database->getList($key)) {
            return $dbkey->linsert($where, $pivot, $value);
        }

        return 0;
    }

    /**
     * @link http://redis.io/commands/llen
     */
    public function llen($key)
    {
        if ($dbkey = $this->database->getList($key)) {
            return $dbkey->llen();
        }

        return 0;
    }

    /**
     * @link http://redis.io/commands/lpop
     */
    public function lpop($key)
    {
        if ($dbkey = $this->database->getList($key)) {
            return $dbkey->lpop();
        }
    }

    /**
     * @link http://redis.io/commands/lpush
     */
    public function lpush($key, array $values)
    {
        $dbkey = $this->database->getList($key, true);

        $dbkey->lpush($values);
        $length = $dbkey->llen();

        return $length;
    }

    /**
     * @link http://redis.io/commands/lpushx
     */
    public function lpushx($key, $value)
    {
        if ($dbkey = $this->database->getList($key)) {
            $dbkey->lpush(array($value));
            $length = $dbkey->llen();

            return $length;
        }

        return 0;
    }

    /**
     * @link http://redis.io/commands/lrem
     */
    public function lrem($key, $count, $value)
    {
        if ($dbkey = $this->database->getList($key)) {
            return $dbkey->lrem($count, $value);
        }

        return 0;
    }

    /**
     * @link http://redis.io/commands/lset
     */
    public function lset($key, $index, $value)
    {
        if (!$dbkey = $this->database->getList($key)) {
            throw new InvalidArgumentException("no such key");
        }

        $dbkey->lset($index, $value);

        return true;
    }

    /**
     * @link http://redis.io/commands/ltrim
     */
    public function ltrim($key, $start, $stop)
    {
        if ($dbkey = $this->database->getList($key)) {
            $dbkey->ltrim($start, $stop);
        }

        return true;
    }

    /**
     * @link http://redis.io/commands/rpop
     */
    public function rpop($key)
    {
        if ($dbkey = $this->database->getList($key)) {
            return $dbkey->rpop();
        }
    }

    /**
     * @link http://redis.io/commands/lpush
     */
    public function rpush($key, array $values)
    {
        $dbkey = $this->database->getList($key, true);

        $dbkey->rpush($values);
        $length = $dbkey->llen();

        return $length;
    }

    /**
     * @link http://redis.io/commands/rpushx
     */
    public function rpushx($key, $value)
    {
        if ($dbkey = $this->database->getList($key)) {
            $dbkey->rpush(array($value));
            $length = $dbkey->llen();

            return $length;
        }

        return 0;
    }

    /**
     * @link http://redis.io/commands/lrange
     */
    public function lrange($key, $start, $stop)
    {
        if ($dbkey = $this->database->getList($key)) {
            return $dbkey->lrange($start, $stop);
        }
    }

    // Hashes

    /**
     * @link http://redis.io/commands/hdel
     */
    public function hdel($key, array $fields)
    {
        if ($dbkey = $this->database->getHash($key)) {
            return $dbkey->hdel($fields);
        }

        return 0;
    }

    /**
     * @link http://redis.io/commands/hexists
     */
    public function hexists($key, $field)
    {
        if ($dbkey = $this->database->getHash($key)) {
            return $dbkey->hexists($field);
        }

        return 0;
    }

    /**
     * @link http://redis.io/commands/hget
     */
    public function hget($key, $field)
    {
        if ($dbkey = $this->database->getHash($key)) {
            return $dbkey->hget($field);
        }
    }

    /**
     * @link http://redis.io/commands/hgetall
     */
    public function hgetall($key)
    {
        if ($dbkey = $this->database->getHash($key)) {
            return $dbkey->hgetall();
        }

        return array();
    }

    /**
     * @link http://redis.io/commands/hkeys
     */
    public function hkeys($key)
    {
        if ($dbkey = $this->database->getHash($key)) {
            return $dbkey->hkeys();
        }

        return array();
    }

    /**
     * @link http://redis.io/commands/hlen
     */
    public function hlen($key)
    {
        if ($dbkey = $this->database->getHash($key)) {
            return $dbkey->hlen();
        }

        return 0;
    }

    /**
     * @link http://redis.io/commands/hset
     */
    public function hset($key, $field, $value)
    {
        return $this->database->getHash($key, true)->hset($field, $value);
    }
}
