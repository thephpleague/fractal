<?php

namespace League\Fractal\Test\Hal;

use League\Fractal\Hal\HalInterface;
use League\Fractal\TransformerAbstract;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;

class HalTest extends \PHPUnit_Framework_TestCase
{
    public function testHalOutputIsAdded()
    {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());

        $book = Book::getBook();
        $resource = new Item($book, new BookTransformer());

        $data = $manager->createData($resource)->toArray();
        $this->assertArrayHasKey('_links', $data);

        $data = $data['_links'];
        $this->assertArrayHasKey('self', $data);
        $this->assertArrayHasKey('next', $data);
        $this->assertArrayHasKey('previous', $data);
    }
}

class Book implements HalInterface
{
    public $id;
    public $title;
    public $yr;
    public $author_name;
    public $author_email;

    public static function getBook(){
        $book = new Book();
        $book->id = 1;
        $book->title = 'the title';
        $book->yr = 'the yr';
        $book->author_name = 'test';
        $book->author_email = 'test@test.pt';

        return $book;
    }

    public function getSelfLink()
    {
        return 'self.html' . $this->id;
    }

    public function getNextLink()
    {
        return 'next.html' . $this->id;
    }

    public function getPreviousLink()
    {
        return 'previous.html' . $this->id;
    }

    public function getCurries()
    {
        return [];
    }
}

class BookTransformer extends TransformerAbstract
{
    public function transform(Book $book)
    {
        return [
            'id'      => (int) $book->id,
            'title'   => $book->title,
            'year'    => $book->yr,
            'author'  => [
                'name'  => $book->author_name,
                'email' => $book->author_email
            ]
        ];
    }
}
