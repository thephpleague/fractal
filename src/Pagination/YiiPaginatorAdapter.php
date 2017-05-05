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

use yii\data\Pagination;

/**
 * A paginator adapter for yiisoft/yii2.
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class YiiPaginatorAdapter implements PaginatorInterface
{
    /**
     * The paginator instance.
     *
     * @var \yii\data\Pagination
     */
    protected $paginator;

    /**
     * Create a new yii framework pagination adapter.
     *
     * @param \yii\data\Pagination $paginator
     */
    public function __construct(Pagination $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * Get the current page.
     *
     * @return integer
     */
    public function getCurrentPage()
    {
        return $this->paginator->getPage() + 1;
    }

    /**
     * Get the last page.
     *
     * @return integer
     */
    public function getLastPage()
    {
        return $this->paginator->getPageCount();
    }

    /**
     * Get the total.
     *
     * @return integer
     */
    public function getTotal()
    {
        return $this->paginator->totalCount;
    }

    /**
     * Get the count.
     *
     * @return integer
     */
    public function getCount()
    {
        if ($this->getTotal() < $this->getPerPage()) {
            return $this->getTotal();
        } elseif ($this->getCurrentPage() < $this->getLastPage()) {
            return $this->getPerPage();
        } else {
            return $this->getTotal() - ($this->getCurrentPage() - 1) * $this->getPerPage();
        }
    }

    /**
     * Get the number per page.
     *
     * @return integer
     */
    public function getPerPage()
    {
        return $this->paginator->getPageSize();
    }

    /**
     * Get the url for the given page.
     *
     * @param integer $page
     *
     * @return string
     */
    public function getUrl($page)
    {
        return $this->paginator->createUrl($page - 1);
    }

    /**
     * Get the paginator instance.
     *
     * @return \yii\data\Pagination
     */
    public function getPaginator()
    {
        return $this->paginator;
    }
}
