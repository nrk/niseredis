<?php

/*
 * This file is part of the Niseredis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Niseredis;

use InvalidArgumentException;
use Niseredis\Database;
use Niseredis\Engine;

class Redis
{
    private $databases;

    public function __construct()
    {
        $database = $this->initializeDatabases();
        $this->engine = new Engine\Engine($database);
    }

    protected function initializeDatabases($count = 16)
    {
        for ($i = 0; $i < $count; $i++) {
            $this->databases[] = new Database\Database();
        }

        return $this->databases[0];
    }

    protected function getDatabaseByIndex($index)
    {
        if ($index < 0 || $index >= count($this->databases)) {
            throw new OutOfRangeException('invalid DB index');
        }

        return $this->databases[$index];
    }


    // Keys

    public function del(/* $key, [$key ...] */)
    {
        return $this->engine->getDatabase()->delete(func_get_args());
    }

    public function exists($key)
    {
        return (int) $this->engine->getDatabase()->exists($key);
    }

    public function expire($key, $seconds)
    {
        return (int) $this->engine->getDatabase()->expireat($key, microtime(true) + $seconds);
    }

    public function move($key, $dbindex)
    {
        $database = $this->getDatabaseByIndex($dbindex);
        $result = $this->engine->getDatabase()->move($key, $database);

        return (int) $result;
    }

    public function persist($key)
    {
        return (int) $this->engine->getDatabase()->persist($key);
    }

    public function keys($pattern)
    {
        return $this->engine->getDatabase()->keys($pattern);
    }

    public function randomkey()
    {
        return $this->engine->getDatabase()->random();
    }

    public function rename($key, $newkey)
    {
        $this->engine->getDatabase()->rename($key, $newkey);

        return true;
    }

    public function renamenx($key, $newkey)
    {
        $database = $this->engine->getDatabase();

        if ($database->exists($newkey)) {
            return 0;
        }

        $database->rename($key, $newkey);

        return 1;
    }

    public function ttl($key)
    {
        return round($this->engine->getDatabase()->ttl($key));
    }

    public function type($key)
    {
        return $this->engine->getDatabase()->type($key);
    }


    // Strings

    public function append($key, $value)
    {
        return $this->engine->append($key, $value);
    }

    public function bitcount($key, $start = 0, $end = -1)
    {
        return $this->engine->bitcount($key, $start, $end);
    }

    public function decr($key)
    {
        return $this->engine->incrby($key, -1);
    }

    public function decrby($key, $decrement)
    {
        return $this->engine->incrby($key, -$decrement);
    }

    public function incr($key)
    {
        return $this->engine->incrby($key, 1);
    }

    public function incrby($key, $increment)
    {
        return $this->engine->incrby($key, $increment);
    }

    public function incrbyfloat($key, $increment)
    {
        return $this->engine->incrbyfloat($key, $increment);
    }

    public function get($key)
    {
        return $this->engine->get($key);
    }

    public function getbit($key, $offset)
    {
        return $this->engine->getbit($key, $offset);
    }

    public function getrange($key, $start, $end)
    {
        return $this->engine->getrange($key, $start, $end);
    }

    public function getset($key, $value)
    {
        return $this->engine->getset($key, $value);
    }

    public function mget(/* $key, [$key ...]*/)
    {
        return $this->engine->mget(func_get_args());
    }

    protected function msetimpl($command, array $arguments)
    {
        $count = count($arguments);

        if ($count % 2 !== 0) {
            throw new InvalidArgumentException("wrong number of arguments for mget command");
        }

        $args = array();

        for ($i = 0; $i < $count; $i++) {
            $args[$arguments[$i]] = $arguments[++$i];
        }

        return $this->engine->$command($args);
    }

    public function mset(/* $key, [$key ...]*/)
    {
        return $this->msetimpl('mset', func_get_args());
    }

    public function msetnx(/* $key, [$key ...]*/)
    {
        return $this->msetimpl('msetnx', func_get_args());
    }

    public function set($key, $value)
    {
        return $this->engine->set($key, $value);
    }

    public function setbit($key, $offset, $value)
    {
        return $this->engine->setbit($key, $offset, $value);
    }

    public function setnx($key, $value)
    {
        return $this->engine->setnx($key, $value);
    }

    public function setrange($key, $offset, $value)
    {
        return $this->engine->setrange($key, $offset, $value);
    }

    public function strlen($key)
    {
        return $this->engine->strlen($key);
    }


    // List

    public function lindex($key, $index)
    {
        return $this->engine->lindex($key, $index);
    }

    public function linsert($key, $where, $pivot, $value)
    {
        return $this->engine->linsert($key, $where, $pivot, $value);
    }

    public function llen($key)
    {
        return $this->engine->llen($key);
    }

    public function lpop($key)
    {
        return $this->engine->lpop($key);
    }

    public function lpush(/* $key, $value [, $value, ... ] */)
    {
        $arguments = func_get_args();
        $llen = $this->engine->lpush(array_shift($arguments), $arguments);

        return $llen;
    }

    public function lpushx($key, $value)
    {
        return $this->engine->lpushx($key, $value);
    }

    public function lrem($key, $count, $value)
    {
        return $this->engine->lrem($key, $count, $value);
    }

    public function lset($key, $index, $value)
    {
        return $this->engine->lset($key, $index, $value);
    }

    public function ltrim($key, $start, $stop)
    {
        return $this->engine->ltrim($key, $start, $stop);
    }

    public function lrange($key, $start, $stop)
    {
        return $this->engine->lrange($key, $start, $stop);
    }

    public function rpop($key)
    {
        return $this->engine->rpop($key);
    }

    public function rpush(/* $key, $value [, $value, ... ] */)
    {
        $arguments = func_get_args();
        $llen = $this->engine->rpush(array_shift($arguments), $arguments);

        return $llen;
    }

    public function rpushx($key, $value)
    {
        return $this->engine->rpushx($key, $value);
    }


    // Hash

    /**
     * @link http://redis.io/commands/hdel
     */
    public function hdel(/* $key, $field [, $field, ... ] */)
    {
        $arguments = func_get_args();
        $deleted = $this->engine->hdel(array_shift($arguments), $arguments);

        return $deleted;
    }

    /**
     * @link http://redis.io/commands/hget
     */
    public function hget($key, $field)
    {
        return $this->engine->hget($key, $field);
    }

    /**
     * @link http://redis.io/commands/hexists
     */
    public function hexists($key, $field)
    {
        return $this->engine->hexists($key, $field);
    }

    /**
     * @link http://redis.io/commands/hgetall
     */
    public function hgetall($key)
    {
        $response = array();

        foreach ($this->engine->hgetall($key) as $key => $value) {
            $response[] = $key;
            $response[] = $value;
        }

        return $response;
    }

    /**
     * @link http://redis.io/commands/hincrby
     */
    public function hincrby($key, $field, $increment)
    {
        return $this->engine->hincrby($key, $field, $increment);
    }

    /**
     * @link http://redis.io/commands/hincrbyfloat
     */
    public function hincrbyfloat($key, $field, $increment)
    {
        return $this->engine->hincrbyfloat($key, $field, $increment);
    }

    /**
     * @link http://redis.io/commands/hkeys
     */
    public function hkeys($key)
    {
        return $this->engine->hkeys($key);
    }

    /**
     * @link http://redis.io/commands/hlen
     */
    public function hlen($key)
    {
        return $this->engine->hlen($key);
    }

    /**
     * @link http://redis.io/commands/hmget
     */
    public function hmget(/* $key, $field [, $field ...]*/)
    {
        $arguments = func_get_args();
        $values = $this->engine->hmget(array_shift($arguments), $arguments);

        return $values;
    }

    /**
     * @link http://redis.io/commands/hmset
     */
    public function hmset(/* $key, $field, $value [, $field, $value, ...]*/)
    {
        $argv = func_get_args();
        $key = array_shift($argv);
        $count = count($argv);

        if ($count % 2 !== 0) {
            throw new InvalidArgumentException("wrong number of arguments for hmset command");
        }

        $arguments = array();

        for ($i = 0; $i < $count; $i++) {
            $arguments[$argv[$i]] = $argv[++$i];
        }

        $this->engine->hmset($key, $arguments);

        return true;
    }

    /**
     * @link http://redis.io/commands/hset
     */
    public function hset($key, $field, $value)
    {
        return $this->engine->hset($key, $field, $value);
    }

    /**
     * @link http://redis.io/commands/hsetnx
     */
    public function hsetnx($key, $field, $value)
    {
        return $this->engine->hsetnx($key, $field, $value);
    }

    /**
     * @link http://redis.io/commands/hvals
     */
    public function hvals($key)
    {
        return $this->engine->hvals($key);
    }

    // Connection

    public function auth($password)
    {
        return true;
    }

    public function echo_($message)
    {
        return $message;
    }

    public function ping()
    {
        return 'PONG';
    }

    public function quit()
    {
        $this->select(0);

        return true;
    }

    public function select($index)
    {
        $database = $this->getDatabaseByIndex($index);
        $this->engine->setDatabase($database);

        return true;
    }


    // Server

    public function dbsize()
    {
        return count($this->engine->getDatabase());
    }

    public function flushall()
    {
        foreach ($this->databases as $database) {
            $database->flush();
        }

        return true;
    }

    public function flushdb()
    {
        $this->engine->getDatabase()->flush();

        return true;
    }

    public function info($section = null)
    {
        $info = array(
            'Server' => array(
                'redis_version'  => '2.8.0',
                'redis_git_sha1' => '4e6d34bd',
                'redis_git_dirty'=> '0',
                'redis_build_id' => 'd86011f0d21b246',
                'redis_mode'     => 'standalone',
                'tcp_port'       => '6379',
            ),
            'Keyspace' => array(),
        );

        foreach ($this->databases as $index => $database) {
            if ($keys = count($database)) {
                $info['Keyspace']["db$index"] = "keys=$keys,expires=0,avg_ttl=0";
            }
        }

        $buffer = '';

        foreach ($info as $sectionKey => $sectionData) {
            if ($section && strcasecmp($sectionKey, $section)) {
                continue;
            }

            $buffer .= "# $sectionKey\n";
            foreach ($sectionData as $k => $v) {
                $buffer .= "$k:$v\n";
            }
            $buffer .= "\n";
        }

        return $buffer;
    }

    public function time()
    {
        $microtime = explode(" ", microtime());

        return array($microtime[1], (string) ($microtime[0] * 1000000));
    }
}
