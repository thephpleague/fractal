<?php namespace League\Fractal;

class ItemResource implements ResourceInterface
{

    protected $processor;
    protected $data;

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
