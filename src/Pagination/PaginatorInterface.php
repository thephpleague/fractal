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
 * A common interface for paginators to use
 *
 * @author Marc Addeo <marcaddeo@gmail.com>
 */
interface PaginatorInterface
{
    public function getCurrentPage();
    public function getLastPage();
    public function getTotal();
    public function getCount();
    public function getPerPage();
    public function getUrl($page);
}
