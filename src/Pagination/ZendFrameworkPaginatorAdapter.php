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

use Zend\Paginator\Paginator;

/**
 * A paginator adapter for zendframework/zend-paginator
 *
 * @author Abdul Malik Ikhsan <samsonasik@gmail.com>
 */
class ZendFrameworkPaginatorAdapter implements PaginatorInterface
{
    /**
     * @var \Zend\Paginator\Paginator
     */
    protected $paginator;

    /**
     * Generate urls
     *
     * @var callable
     */
    protected $routeGenerator;

    /**
     * @param \Zend\Paginator\Paginator $paginator
     * @param callable $routeGenerator
     */
    public function __construct(Paginator $paginator, $routeGenerator)
    {
        $this->paginator = $paginator;
        $this->routeGenerator = $routeGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPage()
    {
        return $this->paginator->getCurrentPageNumber();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastPage()
    {
        return $this->paginator->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        return $this->paginator->getTotalItemCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->paginator->getCurrentItemCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getPerPage()
    {
        return $this->paginator->getItemCountPerPage();
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl($page)
    {
        return call_user_func($this->routeGenerator, $page);
    }
}
