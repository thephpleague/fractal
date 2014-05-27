<?php

use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use League\Fractal\Scope;
use League\Fractal\Test\Stub\Transformer\GenericBookTransformer;

class ArraySerializerTest extends PHPUnit_Framework_TestCase {

    public function testSerializingItemResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new ArraySerializer());

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
            '_author' => array(
                'name' => 'Dave',
            ),
        );

        $resource = new Item($bookData, new GenericBookTransformer(), 'book');
        $scope = new Scope($manager, $resource);

        $expected = array(
            'title' => 'Foo',
            'year' => 1991,
            'author' => array(
                'name' => 'Dave',
            ),
            'includes' => array('author'),
        );

        $this->assertEquals($expected, $scope->toArray());
    }

    public function testSerializingCollectionResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new ArraySerializer());

        $booksData = array(
            array(
                'title' => 'Foo',
                'year' => '1991',
                '_author' => array(
                    'name' => 'Dave',
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => '1997',
                '_author' => array(
                    'name' => 'Bob',
                ),
            ),
        );

        $resource = new Collection($booksData, new GenericBookTransformer(), 'book');
        $scope = new Scope($manager, $resource);

        $expected = array(
            array(
                'title' => 'Foo',
                'year' => 1991,
                'author' => array(
                    'name' => 'Dave',
                ),
            ),
            array(
                'title' => 'Bar',
                'year' => 1997,
                'author' => array(
                    'name' => 'Bob',
                ),
            ),
            'includes' => array('author'),
        );

        $this->assertEquals($expected, $scope->toArray());
    }


    public function tearDown()
    {
        Mockery::close();
    }

}
