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

use Illuminate\Pagination\Paginator;

/**
 * A paginator adapter for illuminate/pagination.
 *
 * @author Marc Addeo <marcaddeo@gmail.com>
 */
class IlluminatePaginatorAdapter implements PaginatorInterface
{
    /**
     * The paginator instance.
     *
     * @var \Illuminate\Pagination\Paginator
     */
    protected $paginator;

    /**
     * Create a new illuminate pagination adapter.
     *
     * @param \Illuminate\Pagination\Paginator $paginator
     *
     * @return void
     */
    public function __construct(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->paginator->getCurrentPage();
    }

    /**
     * Get the last page.
     *
     * @return int
     */
    public function getLastPage()
    {
        return $this->paginator->getLastPage();
    }

    /**
     * Get the total.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->paginator->getTotal();
    }

    /**
     * Get the count.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->paginator->count();
    }

    /**
     * Get the number per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->paginator->getPerPage();
    }

    /**
     * Get the url for the given page.
     *
     * @param int $page
     *
     * @return string
     */
    public function getUrl($page)
    {
        return $this->paginator->getUrl($page);
    }

    /**
     * Get the paginator instance.
     *
     * @return \Illuminate\Paginator\Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }
}
