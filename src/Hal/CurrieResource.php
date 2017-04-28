<?php

namespace League\Fractal\Hal;

use League\Fractal\Helper\Validator;

class CurrieResource
{
    private $key;

    private $href;

    public function __construct($key, $href)
    {
        Validator::validateParamString('key', $key);
        Validator::validateParamString('href', $href);

        $this->key = $key;
        $this->href = $href;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
}
