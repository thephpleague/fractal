<?php namespace League\Fractal\Test\Dto;

class Person
{
    public $name;
    public $meta;

    public function __construct($name, array $meta = null)
    {
        $this->name = $name;
        $this->meta = $meta;
    }

    public static function make($name, array $meta = null)
    {
        return new self($name, $meta);
    }
}
