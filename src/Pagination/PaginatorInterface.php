<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <me@philsturgeon.uk>
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
    /**
     * @return integer
     */
    public function getCurrentPage();

    /**
     * @return integer
     */
    public function getLastPage();

    /**
     * @return integer
     */
    public function getTotal();

    /**
     * @return integer
     */
    public function getCount();

    /**
     * @return integer
     */
    public function getPerPage();

    /**
     * @param integer $page
     *
     * @return string
     */
    public function getUrl($page);
}
