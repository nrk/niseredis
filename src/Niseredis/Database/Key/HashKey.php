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

class HashKey implements KeyInterface
{
    protected $dictionary;

    public function __construct()
    {
        $this->dictionary = array();
    }

    public function getType()
    {
        return 'hash';
    }

    public function getValue()
    {
        return $this->dictionary;
    }

    public function isEmpty()
    {
        return empty($this->dictionary);
    }
}
