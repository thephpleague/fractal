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
 * A paginator adapter for PhalconPHP/pagination.
 *
 * @author Thien Tran <fcduythien@gmail.com>
 * 
 */
class PhalconFrameworkPaginatorAdapter implements PaginatorInterface
{
    /**
     * A slice of the result set to show in the pagination
     *
     * @var \Phalcon\Paginator\AdapterInterface
     */
    private $paginator;

    
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
        return $this->getPaginator()->current;
    }

    /**
     * Get the last page.
     *
     * @return int
     */
    public function getLastPage()
    {
        return $this->getPaginator()->last;
    }

    /**
     * Get the total.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->getPaginator()->total_items;
    }

    /**
     * Get the count.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->getPaginator()->total_pages;
    }

    /**
     * Get the number per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->getPaginator()->items->count();
    }

    /**
     * Get the next.
     *
     * @return int
     */ 
    public function getNext()
    {
        return $this->getPaginator()->next;
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
        throw new \Exception("NotYetImplementedException");
    }

    /**
     * Get the paginate object.
     * 
     * @return object
     */
    public function getPaginator()
    {
        return $this->paginator;
    }
}
