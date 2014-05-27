<?php

use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use League\Fractal\Scope;
use League\Fractal\Test\Stub\Transformer\GenericBookTransformer;

class JsonApiSerializerTest extends PHPUnit_Framework_TestCase {

    public function testSerializingItemResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new JsonApiSerializer());

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
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                )
            ),
            'linked' => array(
                'author' => array(
                    array(
                        'name' => 'Dave'
                    )
                )
            )
        );

        $this->assertEquals($expected, $scope->toArray());
    }

    public function testSerializingCollectionResource()
    {
        $manager = new Manager();
        $manager->parseIncludes('author');
        $manager->setSerializer(new JsonApiSerializer());

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
            'book' => array(
                array(
                    'title' => 'Foo',
                    'year' => 1991,
                ),
                array(
                    'title' => 'Bar',
                    'year' => 1997,
                ),
            ),
            'linked' => array(
                'author' => array(
                    array('name' => 'Dave'),
                    array('name' => 'Bob'),
                )
            )
        );

        $this->assertEquals($expected, $scope->toArray());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The $resourceKey parameter must be provided when using League\Fractal\Serializer\JsonApiSerializer
     **/
    public function testResourceKeyMissing()
    {
        $manager = new Manager();
        $manager->setSerializer(new JsonApiSerializer());

        $bookData = array(
            'title' => 'Foo',
            'year' => '1991',
        );

        $resource = new Item($bookData, new GenericBookTransformer());
        $scope = new Scope($manager, $resource);

        $scope->toArray();
    }



    public function tearDown()
    {
        Mockery::close();
    }

}
