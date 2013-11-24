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
use UnexpectedValueException;

class StringKey implements KeyInterface
{
    protected $value;

    public function __construct($value = '')
    {
        $this->value = (string) $value;
    }

    public function getType()
    {
        return 'string';
    }

    public function getValue()
    {
        return (string) $this->value;
    }

    public function isEmpty()
    {
        return strlen($this->value) === 0;
    }

    /**
     * @link http://redis.io/commands/append
     */
    public function append($value)
    {
        $this->value .= (string) $value;

        return $this->strlen();
    }

    /**
     * @link http://redis.io/commands/getrange
     */
    public function getrange($start, $end)
    {
        if ($start < 0) {
            $start = max($this->strlen() + $start, 0);
        }

        if ($end < 0) {
            $end = min($this->strlen() + $end, $this->strlen());
        }

        if (false !== $substr = substr($this->value, $start, $end - $start + 1)) {
            return $substr;
        }

        return '';
    }

    /**
     * @link http://redis.io/commands/setrange
     */
    public function setrange($offset, $value)
    {
        if ($offset < 0) {
            throw new InvalidArgumentException("offset is out of range");
        }

        if ($offset > $this->strlen()) {
            $this->value = str_pad($this->value, $offset, "\0");
        }

        $this->value = substr_replace($this->value, $value, $offset, strlen($value));

        return $this->strlen();
    }

    /**
     * @link http://redis.io/commands/strlen
     */
    public function strlen()
    {
        return strlen($this->value);
    }

    /**
     * @link http://redis.io/commands/incr
     * @link http://redis.io/commands/incrby
     * @link http://redis.io/commands/decr
     * @link http://redis.io/commands/decrby
     */
    public function incrby($increment)
    {
        if ("$increment" != (int) $increment) {
            throw new UnexpectedValueException("value is not an integer or out of range");
        }

        $value = $this->value ?: 0;

        if (!is_numeric($value) ||
            stripos($value, '.') !== false ||
            stripos($value, 'e') !== false
        ) {
            throw new UnexpectedValueException("value is not an integer or out of range");
        }

        $this->value = $value += $increment;

        return (string) $value;
    }

    /**
     * @link http://redis.io/commands/incrbyfloat
     */
    public function incrbyfloat($increment)
    {
        if ("$increment" != (float) $increment || !is_numeric($this->value)) {
            throw new UnexpectedValueException("value is not an integer or out of range");
        }

        $this->value += $increment;

        return (string) $this->value;
    }

    /**
     * @link http://redis.io/commands/setbit
     */
    public function setbit($offset, $value)
    {
        if ($value != 0 && $value != 1) {
            throw new InvalidArgumentException('bit is not an integer or out of range');
        }

        $bytepos = (int) ($offset / 8);
        $this->value = str_pad($this->value, $bytepos + 1, "\0");

        $char = unpack('C', $this->value[$bytepos]);
        $byte = str_pad(decbin($char[1]), 8, '0', STR_PAD_LEFT);

        $old = $byte[$offset % 8];
        $byte[$offset % 8] = $value;

        $this->value[$bytepos] = pack('C', bindec($byte));

        return (int) $old;
    }

    /**
     * @link http://redis.io/commands/getbit
     */
    public function getbit($offset)
    {
        $bytepos = (int) ($offset / 8);

        if ($bytepos >= $this->strlen()) {
            return 0;
        }

        $char = unpack('C', $this->value[$bytepos]);
        $byte = str_pad(decbin($char[1]), 8, '0', STR_PAD_LEFT);

        return (int) $byte[$offset % 8];
    }

    /**
     * @todo Fix $stard and $end ranges
     * @link http://redis.io/commands/getbit
     */
    public function bitcount($start = 0, $end = -1)
    {
        if ($start < 0) {
            $start = $this->strlen() + $start;
        }

        if ($end < 0) {
            $end = $this->strlen() + $end;
        }

        $count = 0;

        for ($i = $start; $i <= $end; $i++) {
            $char = unpack('C', $this->value[$i]);
            $byte = str_pad(decbin($char[1]), 8, '0', STR_PAD_LEFT);

            for ($b = 0; $b < 8; $b++) {
                if ($byte[$b] === '1') {
                    $count++;
                }
            }
        }

        return $count;
    }
}
