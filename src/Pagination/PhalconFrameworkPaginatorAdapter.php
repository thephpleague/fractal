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
 * A paginator adapter for illuminate/pagination.
 *
 * @author Thien Tran <fcduythiengmail.com>
 */
class PhalconFrameworkPaginatorAdapter implements PaginatorInterface
{
    /**
     * A slice of the result set to show in the pagination
     *
     * @var \Phalcon\Paginator\AdapterInterface
     */
    protected $paginator;

    /***
     *
     * @return void
     */
    public function __construct($paginator)
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
        return $this->getPaginate()->current;
    }

    /**
     * Get the last page.
     *
     * @return int
     */
    public function getLastPage()
    {
        return $this->getPaginate()->last;
    }

    /**
     * Get the total.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->getPaginate()->total_items;
    }

    /**
     * Get the count.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->getPaginate()->total_pages;
    }

    /**
     * Get the number per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->paginator->getLimit();

    }

    /**
     * Get the next.
     *
     * @return int
     */ 
    public function getNext()
    {
        return $this->getPaginate()->next;
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
        //PhalconPHP not yet
    }

    /**
     * Get the paginator instance.
     *
     */
    public function getPaginate()
    {
        
        return $this->paginator->getPaginate();
    }
}
