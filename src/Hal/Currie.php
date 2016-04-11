<?php

namespace League\Fractal\Hal;

use League\Fractal\Helper\Validator;

class Currie
{
    const RELATION = '/{rel}';

    private $name;

    private $href;

    private $templated = false;

    private $resources;

    public function __construct($name, $href, $templated, array $resources)
    {
        Validator::validateParamString('name', $name);
        Validator::validateParamString('href', $href);
        Validator::validateParamBool('templated', $templated);

        $this->name = $name;
        $this->href = $href;
        $this->templated = $templated;
        $this->resources = $resources;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->href ? $this->href . static::RELATION : '';
    }

    /**
     * @return boolean
     */
    public function isTemplated()
    {
        return $this->templated;
    }

    /**
     * @return CurrieResource[]
     */
    public function getResources()
    {
        return $this->resources;
    }
}
