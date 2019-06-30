<?php

namespace League\Fractal\Pagination;

class SimplePaginationAdapter implements PaginatorInterface
{

    /**
     * @var int The total number of items in this list
     */
    protected $total;

    /**
     * @var int The count of items in the result
     */
    protected $itemCount;

    /**
     * @var int The current item key
     */
    protected $current;

    /**
     * @var int The number of items per page
     */
    protected $perPage;

    /**
     * @var callable
     */
    protected $urlFactory;

    /**
     * SimplePaginationAdapter constructor.
     * @param int $currentPage The current page, >=1
     * @param int $itemCount The count of items on the current page
     * @param int $perPage The maximum count we could have per page
     * @param int $total The total number of items
     * @param callable $urlFactory function(int $currentPage)
     */
    public function __construct(int $currentPage, int $itemCount, int $perPage, int $total, callable $urlFactory)
    {
        $this->current = $currentPage;
        $this->itemCount = $itemCount;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->urlFactory = $urlFactory;
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->current;
    }

    /**
     * Get the last page.
     *
     * @return int
     */
    public function getLastPage()
    {
        return $this->total / $this->perPage;
    }

    /**
     * Get the total.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Get the count.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->itemCount;
    }

    /**
     * Get the number per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
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
        return ($this->urlFactory)($page);
    }
}