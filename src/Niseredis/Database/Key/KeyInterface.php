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

interface KeyInterface
{
    public function getType();

    public function getValue();

    public function getExpiration();

    public function setExpiration($time);

    public function isExpired();

    public function isEmpty();
}
