<?php namespace League\Fractal;

use Illuminate\Pagination\Paginator;

class PaginatorResource implements ResourceInterface
{
    protected $paginator;
    protected $processor;
    protected $data;

    public function __construct(Paginator $paginator, $processor)
    {
        $this->paginator = $paginator;
        $this->processor = $processor;
        $this->data = $paginator->getCollection();
    }
    
    public function getData()
    {
        return $this->data;
    }

    public function getPaginator()
    {
        return $this->paginator;
    }

    public function getProcessor()
    {
        return $this->processor;
    }
}
