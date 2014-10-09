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
 * A paginator adapter for illuminate/pagination
 *
 * @author Marc Addeo <marcaddeo@gmail.com>
 */
class IlluminatePaginatorAdapter implements PaginatorInterface
{
    /**
     * The paginator
     * @var object
     */
    protected $paginator;

    /**
     * Setup our adapter
     *
     * @param Paginator $paginator
     */
    public function __construct(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * Get current page
     * @return integer
     */
    public function getCurrentPage()
    {
        return $this->paginator->getCurrentPage();
    }

    /**
     * Get last page
     * @return integer
     */
    public function getLastPage()
    {
         return $this->paginator->getLastPage();
    }

    /**
     * Get total
     * @return integer
     */
    public function getTotal()
    {
         return $this->paginator->getTotal();
    }

    /**
     * Get count
     * @return integer
     */
    public function getCount()
    {
        return $this->paginator->count();
    }

    /**
     * Get per page
     * @return integer
     */
    public function getPerPage()
    {
        return $this->paginator->getPerPage();
    }

    /**
     * Get url for the given page
     * @param  integer $page
     * @return string
     */
    public function getUrl($page)
    {
        return $this->paginator->getUrl($page);
    }

    /**
     * Get the paginator
     * @return object
     */
    public function getPaginator()
    {
        return $this->paginator;
    }
}
