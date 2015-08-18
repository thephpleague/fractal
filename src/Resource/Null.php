<?php namespace League\Fractal\Resource;

class Null extends ResourceAbstract
{
    /**
     * Get the data.
     *
     * @return mixed
     */
    public function getData()
    {
        return null;
    }
}
