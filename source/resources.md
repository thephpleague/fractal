---
layout: layout
title: Resources
---

# Resources

Resources are objects that represent data, and have knowledge of a "Transformer", which is 
an object or callback that will know how to output the data.

Two types of resource exist:

* **League\Fractal\Resource\Item** - A singular resource, probably one entry in a data store
* **League\Fractal\Resource\Collection** - A collection of resources

The `Item` and `Collection` constructors will take any kind of data you wish to send it
as the first argument, and then a "transformer" as the second argument. 

These examples use callback transformers instead of creating classes, purely for demonstrative 
purposes.

### Item Example

~~~.language-php
use Acme\Model\Book;
use League\Fractal;

$book = Book::find($id);

$resource = new Fractal\Resource\Item($book, function(Book $book) {
    return [
        'id'      => (int) $book->id,
        'title'   => $book->title,
        'year'    => (int) $book->yr,
        'links'   => [
            [
                'rel' => 'self',
                'uri' => '/books/'.$book->id,
            ]
        ]
    ];
});
~~~

### Collection Example

~~~.language-php
use Acme\Model\Book;
use League\Fractal;

$books = Book::all();

$resource = new Fractal\Resource\Collection($books, function(Book $book) {
    return [
        'id' => (int) $book->id,
        'title' => $book->title,
        'year' => (int) $book->yr,
        'links'   => [
            [
                'rel' => 'self',
                'uri' => '/books/'.$book->id,
            ]
        ]
    ];
});
~~~

In this example `$books` is an array of `Acme\Model\Book` instances, or a collection class 
that implemented [ArrayIterator].

[ArrayIterator]: http://php.net/ArrayIterator
