<?php

use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use League\Fractal\Scope;
use League\Fractal\Test\Stub\Transformer\GenericBookTransformer;

class DataArraySerializerTest extends PHPUnit_Framework_TestCase {

    public function testSerializingItemResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new DataArraySerializer());

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
            'data' => array(
                'title' => 'Foo',
                'year' => 1991,
                'author' => array(
                    'data' => array(
                        'name' => 'Dave',
                    ),
                ),
            ),
            'includes' => array('author'),
        );

        $this->assertEquals($expected, $scope->toArray());
    }

    public function testSerializingCollectionResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new DataArraySerializer());

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
            'data' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                    'author' => array(
                        'data' => array(
                            'name' => 'Dave',
                        ),
                    ),
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                    'author' => array(
                        'data' => array(
                            'name' => 'Bob',
                        ),
                    ),
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
