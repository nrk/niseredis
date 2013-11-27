<?php

/*
 * This file is part of the Niseredis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Niseredis\Database;

use Countable;
use RuntimeException;
use Niseredis\Database\Key\KeyInterface;
use Niseredis\Database\Key\StringKey;
use Niseredis\Database\Key\ListKey;
use Niseredis\Database\Key\SetKey;
use Niseredis\Database\Key\SortedSetKey;
use Niseredis\Database\Key\HashKey;

class Database implements Countable
{
    protected $keyspace;

    public function __construct()
    {
        $this->keyspace = new Keyspace();
    }

    protected function assertType(KeyInterface $key, $type)
    {
        if ($key->getType() !== $type) {
            throw new RuntimeException(
                "Operation against a key holding the wrong kind of value"
            );
        }
    }

    protected function store($key, KeyInterface $object)
    {
        $this->setKey($key, $object);

        return $object;
    }

    public function count()
    {
        return count($this->keyspace);
    }

    public function setKey($key, KeyInterface $object)
    {
        $this->keyspace[$key] = $object;
    }

    public function getKey($key, $type = null)
    {
        if (isset($this->keyspace[$key])) {
            $dbkey = $this->keyspace[$key];

            if ($this->gcKey($key, $dbkey)) {
                return null;
            }

            if ($type) {
                $this->assertType($dbkey, $type);
            }

            return $dbkey;
        }
    }

    public function getKeys(array $keys, $type = null)
    {
        $dbkeys = array();

        foreach ($keys as $key) {
            $dbkeys[$key] = $this->getKey($key, $type);
        }

        return $dbkeys;
    }

    public function delKey($key)
    {
        if ($this->keyspace[$key]) {
            unset($this->keyspace[$key]);

            return true;
        }

        return false;
    }

    protected function gcKey($key, $object = null)
    {
        if (!$object) {
            $object = $this->keyspace[$key];
        }

        if ($object->isEmpty() || $object->isExpired()) {
            unset($this->keyspace[$key]);

            return true;
        }

        return false;
    }

    public function gc()
    {
        foreach ($this->keyspace as $key => $object) {
            $this->gcKey($key, $object);
        }
    }

    public function createString($key, $value = null)
    {
        return $this->store($key, new StringKey($value ?: ''));
    }

    public function createList($key)
    {
        return $this->store($key, new ListKey());
    }

    public function createSet($key)
    {
        return $this->store($key, new SetKey());
    }

    public function createSortedSet($key)
    {
        return $this->store($key, new SortedSetKey());
    }

    public function createHash($key)
    {
        return $this->store($key, new HashKey());
    }

    public function getString($key, $ensure = false)
    {
        if ((!$object = $this->getKey($key, 'string')) && $ensure) {
            $object = $this->createString($key);
        }

        return $object;
    }

    public function getList($key, $ensure = false)
    {
        if ((!$object = $this->getKey($key, 'list')) && $ensure) {
            $object = $this->createList($key);
        }

        return $object;
    }

    public function getSet($key, $ensure = false)
    {
        if ((!$object = $this->getKey($key, 'set')) && $ensure) {
            $object = $this->createSet($key);
        }

        return $object;
    }

    public function getSortedSet($key, $ensure = false)
    {
        if ((!$object = $this->getKey($key, 'zset')) && $ensure) {
            $object = $this->createSortedSet($key);
        }

        return $object;
    }

    public function getHash($key, $ensure = false)
    {
        if ((!$object = $this->getKey($key, 'hash')) && $ensure) {
            $object = $this->createHash($key);
        }

        return $object;
    }

    public function delete(array $keys)
    {
        $deleted = 0;

        foreach ($keys as $key) {
            if ($this->delKey($key)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    public function exists($key)
    {
        return isset($this->keyspace[$key]) && !$this->gcKey($key);
    }

    public function expireat($key, $time)
    {
        if ($object = $this->getKey($key)) {
            $object->setExpiration($time);

            return 1;
        }

        return 0;
    }

    public function ttl($key)
    {
        if ($object = $this->getKey($key)) {
            if (null === $time = $object->getExpiration()) {
                return -1;
            }

            return abs(microtime(true) - $time);
        }

        return -2;
    }

    public function persist($key)
    {
        if (($object = $this->getKey($key)) && null !== $object->getExpiration()) {
            $object->setExpiration(null);

            return 1;
        }

        return 0;
    }

    public function move($key, Database $database)
    {
        if ($this === $database) {
            throw new RuntimeException("source and destination objects are the same");
        }

        if ($object = $this->getKey($key)) {
            $this->delKey($key);
            $database->setKey($key, $object);

            return true;
        }

        return false;
    }

    public function rename($key, $newkey)
    {
        if ($object = $this->getKey($key)) {
            $this->delKey($key);
            $this->setKey($newkey, $object);
        }
    }

    public function random()
    {
        while (count($this->keyspace)) {
            list($key, $object) = $this->keyspace->getRandom();

            if (!$this->gcKey($key, $object)) {
                return $key;
            }
        }
    }

    public function type($key)
    {
        if ($object = $this->getKey($key)) {
            return $object->getType();
        }

        return 'none';
    }

    public function keys($pattern)
    {
        $matches = array();
        $pattern = self::convertGlobToRegex($pattern);

        foreach ($this->keyspace as $key => $object) {
            if (preg_match("/$pattern/", $key) && !$this->gcKey($key, $object)) {
                $matches[] = $key;
            }
        }

        return $matches;
    }

    public function flush()
    {
        $this->keyspace->reset();
    }

    /**
     * @link http://stackoverflow.com/a/17369948
     */
    protected static function convertGlobToRegex($pattern)
    {
        $pattern = trim($pattern, " \t\n\r\0\x0B*");
        $length = strlen($pattern);

        $escaping = false;
        $curlies = 0;
        $regex = '';

        for ($c = 0; $c < $length; $c++) {
            switch ($char = $pattern[$c]) {
                case '*':
                    $regex .= $escaping ? '\\*' : '.*';
                    $ecaping = false;
                    break;

                case '?':
                    $regex .= $escaping ? '\\?' : '.';
                    $ecaping = false;
                    break;

                case '.':
                case '(':
                case ')':
                case '+':
                case '|':
                case '^':
                case '$':
                case '@':
                case '%':
                    $regex .= "\\$char";
                    $escaping = false;
                    break;

                case '\\':
                    if ($escaping) {
                        $regex .= '\\\\';
                    }
                    $escaping = !$escaping;
                    break;

                case '{':
                    if (escaping) {
                        $regex .= '\\{';
                    } else {
                        $regex .= '(';
                        $curlies++;
                    }
                    $escaping = false;
                    break;

                case '}':
                    if ($curlies && !escaping) {
                        $regex .= ')';
                        $curlies--;
                    } else if ($escaping) {
                        $regex .= '\\}';
                    } else {
                        $regex .= '}';
                    }
                    $escaping = false;
                    break;

                case ',':
                    if ($curlies && !escaping) {
                        $regex .= '|';
                    } else if ($escaping) {
                        $regex .= '\\,';
                    } else {
                        $regex .= ',';
                    }
                    break;

                default:
                    $escaping = false;
                    $regex .= $char;
                    break;
            }
        }

        return $regex;
    }
}
