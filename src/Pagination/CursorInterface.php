<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <email@philsturgeon.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Fractal\Pagination;

/**
 * A common interface for cursors to use.
 *
 * @author Isern Palaus <ipalaus@ipalaus.com>
 */
interface CursorInterface
{
    public function getCurrent();
    public function getPrev();
    public function getNext();
    public function getCount();
}
