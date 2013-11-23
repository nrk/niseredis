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

    public function isEmpty()
    {
        return empty($this->members);
    }
}
