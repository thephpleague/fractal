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

use Phalcon\Paginator\RepositoryInterface;

/**
 * A paginator adapter for PhalconPHP/pagination.
 *
 * @author Thien Tran <fcduythien@gmail.com>
 * @author Nikolaos Dimopoulos <nikos@phalconphp.com>
 *
 */
class PhalconFrameworkPaginatorAdapter implements PaginatorInterface
{
    /**
     * A slice of the result set to show in the pagination
     *
     * @var RepositoryInterface
     */
    private $paginator;

    /**
     * PhalconFrameworkPaginatorAdapter constructor.
     *
     * @param RepositoryInterface $paginator
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
        return $this->paginator->getCurrent();
    }

    /**
     * Get the last page.
     *
     * @return int
     */
    public function getLastPage()
    {
        return $this->paginator->getLast();
    }

    /**
     * Get the total.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->paginator->getTotalItems();
    }

    /**
     * Get the count.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->paginator->getTotalItems();
    }

    /**
     * Get the number per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        // $this->paginator->items->count()
        // Because when we use raw sql have not this method
        return count($this->paginator->getItems());
    }

    /**
     * Get the next.
     *
     * @return int
     */ 
    public function getNext()
    {
        return $this->paginator->getNext();
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
        return $page;
    }
}
