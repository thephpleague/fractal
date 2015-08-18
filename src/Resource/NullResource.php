<?php namespace League\Fractal\Resource;

class NullResource extends ResourceAbstract
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
