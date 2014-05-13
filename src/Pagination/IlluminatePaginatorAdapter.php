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

use Illuminate\Pagination\Paginator;

/**
 * A paginator adapter for illuminate/pagination
 *
 * @author Marc Addeo <marcaddeo@gmail.com>
 */
class IlluminatePaginatorAdapter extends Paginator implements PaginatorInterface
{
    /**
     * Setup our adapter
     *
     * @param Illuminate\Pagination\Paginator $paginator
     */
    public function __construct(Paginator $paginator)
    {
        parent::__construct(
            (method_exists($paginator, 'getFactory') ? $paginator->getFactory() : $paginator->getEnvironment()),
            $paginator->getItems(),
            $paginator->getTotal(),
            $paginator->getPerPage()
        );

        /**
         * Pagination contexts need to be setup after we've called our parent
         * constructor
         */
        $this->setupPaginationContext();
    }

    /**
     * Get the number of items for the current page.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count();
    }
}
