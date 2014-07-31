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
            $paginator->getFactory(),
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
    
    /**
     * Override method so includes work with pagination
     * @param  Int $page 
     * @return String       
     */
    public function getUrl($page)
    {
        $parameters = array(
            $this->factory->getPageName() => $page,
        );

        // append the querystring to the pagination links
        $queryStrings = array_except( \Input::query(), $this->factory->getPageName() );
        $this->appends($queryStrings);

        // If we have any extra query string key / value pairs that need to be added
        // onto the URL, we will put them in query string form and then attach it
        // to the URL. This allows for extra information like sortings storage.
        if (count($this->query) > 0)
        {
            $parameters = array_merge($parameters, $this->query);
        }

        $fragment = $this->buildFragment();

        return $this->factory->getCurrentUrl().'?'.http_build_query($parameters, null, '&').$fragment;
    }    
}
