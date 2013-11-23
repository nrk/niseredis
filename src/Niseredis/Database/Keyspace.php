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

use ArrayAccess;
use Countable;
use Niseredis\Database\Key\KeyInterface;

class Keyspace implements ArrayAccess, Countable
{
    private $keyspace;

    public function __construct()
    {
        $this->keyspace = array();
    }

    public function attach($key, KeyInterface $object)
    {
        $this->keyspace[$key] = $object;
    }

    public function detach($key)
    {
        $object = null;

        if (isset($this->keyspace[$key])) {
            $object = $this->keyspace[$key];
            unset($this->keyspace[$key]);
        }

        return $object;
    }

    public function reset()
    {
        $this->keyspace = array();
    }

    public function getRandomKey()
    {
        return array_rand($this->keyspace);
    }

    public function offsetSet($key, $object)
    {
        $this->attach($key, $object);
    }

    public function offsetExists($key)
    {
        return isset($this->keyspace[$key]);
    }

    public function offsetUnset($key)
    {
        unset($this->keyspace[$key]);
    }

    public function offsetGet($key)
    {
        if (isset($this->keyspace[$key])) {
            return $this->keyspace[$key];
        }
    }

    public function count()
    {
        return count($this->keyspace);
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

    public function keys($pattern)
    {
        $matches = array();
        $pattern = self::convertGlobToRegex($pattern);

        foreach ($this->keyspace as $key => $_) {
            if (preg_match("/$pattern/", $key)) {
                $matches[] = $key;
            }
        }

        return $matches;
    }
}
