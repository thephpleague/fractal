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
     */
    public function getCurrentPage(): int
    {
        return $this->current;
    }

    /**
     * Get the last page.
     */
    public function getLastPage(): int
    {
        return (int) floor($this->total / $this->perPage);
    }

    /**
     * Get the total.
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Get the count.
     */
    public function getCount(): int
    {
        return $this->itemCount;
    }

    /**
     * Get the number per page.
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get the url for the given page.
     *
     * @param int $page
     */
    public function getUrl($page): string
    {
        return ($this->urlFactory)($page);
    }
}
