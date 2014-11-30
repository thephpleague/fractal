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

use Pagerfanta\Pagerfanta;

/**
 * A paginator adapter for pagerfanta/pagerfanta
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class PagerfantaPaginatorAdapter implements PaginatorInterface
{
    /**
     * @var Pagerfanta
     */
    protected $pagerfanta;

    /**
     * Generate urls
     *
     * @var callable
     */
    protected $routeGenerator;

    /**
     * @param Pagerfanta $pagerfanta
     */
    public function __construct(Pagerfanta $pagerfanta, $routeGenerator)
    {
        $this->pagerfanta = $pagerfanta;
        $this->routeGenerator = $routeGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPage()
    {
        return $this->pagerfanta->getCurrentPage();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastPage()
    {
         return $this->pagerfanta->getNbPages();
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
         return count($this->pagerfanta);
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return count($this->pagerfanta->getCurrentPageResults());
    }

    /**
     * {@inheritdoc}
     */
    public function getPerPage()
    {
        return $this->pagerfanta->getMaxPerPage();
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl($page)
    {
        return call_user_func($this->routeGenerator, $page);
    }

    /**
     * Get the pagerfanta
     *
     * @return Pagerfanta
     */
    public function getPagerfanta()
    {
        return $this->pagerfanta;
    }

    /**
     * Returns the route generator
     *
     * @return callable
     */
    public function getRouteGenerator()
    {
        return $this->routeGenerator;
    }
}
