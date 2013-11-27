<?php namespace League\Fractal;

class CollectionResource implements ResourceInterface
{
    protected $data;
    protected $processor;

    public function __construct($data, $processor)
    {
        $this->data = $data;
        $this->processor = $processor;
    }
    
    public function getData()
    {
        return $this->data;
    }

    public function getProcessor()
    {
        return $this->processor;
    }
}
