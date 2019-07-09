<?php namespace League\Fractal\Test\Dto;

class Book
{
    public $title;
    public $year;
    public $author;
    public $meta;

    public function __construct($title, $year, Person $author = null, array $meta = null)
    {
        $this->title = $title;
        $this->year = $year;
        $this->author = $author;
        $this->meta = $meta;
    }

    public static function make($title, $year, Person $author = null, array $meta = null)
    {
        return new self($title, $year, $author, $meta);
    }
}
