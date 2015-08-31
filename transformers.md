---
layout: default
permalink: transformers/
title: Transformers
---

# Transformers

In the [Resources](/resources/) section the examples show off callbacks for
transformers, but these are of limited use:

~~~ php
<?php
use Acme\Model\Book;
use League\Fractal;

$books = Book::all();

$resource = new Fractal\Resource\Collection($books, function(Book $book) {
    return [
        'id'      => (int) $book->id,
        'title'   => $book->title,
        'year'    => $book->yr,
        'author'  => [
            'name'  => $book->author_name,
            'email' => $book->author_email,
        ],
        'links'   => [
            [
                'rel' => 'self',
                'uri' => '/books/'.$book->id,
            ]
        ]
    ];
});
~~~

These can be handy in some situations, but most data will need to be transformed multiple
times and in multiple locations, so creating classes to do this work can save code
duplication.

## Classes for Transformers

To reuse transformers (recommended) classes can be defined, instantiated and
passed in place of the callback.

These classes must extend `League\Fractal\TransformerAbstract` and contain at the very
least a method with the name `transform()`.

The method declaration can take mixed input, just like the callbacks:

~~~ php
<?php
namespace Acme\Transformer;

use Acme\Model\Book;
use League\Fractal;

class BookTransformer extends Fractal\TransformerAbstract
{
	public function transform(Book $book)
	{
	    return [
	        'id'      => (int) $book->id,
	        'title'   => $book->title,
	        'year'    => (int) $book->yr,
            'links'   => [
                [
                    'rel' => 'self',
                    'uri' => '/books/'.$book->id,
                ]
            ],
	    ];
	}
}
~~~

Once the Transformer class is defined, it can be passed as an instance in the
resource constructor.

~~~ php
<?php
use Acme\Transformer\BookTransformer;
use League\Fractal;

$resource = new Fractal\Resource\Item($book, new BookTransformer);
$resource = new Fractal\Resource\Collection($books, new BookTransformer);
~~~


## Including Data

Your transformer at this point is mainly just giving you a method to handle array conversion from
your data source (or whatever your model is returning) to a simple array. Including data in an
intelligent way can be tricky as data can have all sorts of relationships. Many developers try to
find a perfect balance between not making too many HTTP requests and not downloading more data than
they need to, so flexibility is also important.

Sticking with the book example, the `BookTransformer`, we might want to normalize our database and take
the two `author_*` fields out and put them in their own table. This include can be optional to reduce the size of the JSON response and is defined like so:

~~~ php
<?php namespace App\Transformer;

use Acme\Model\Book;
use League\Fractal\TransformerAbstract;

class BookTransformer extends TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'author'
    ];

    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(Book $book)
    {
        return [
            'id'    => (int) $book->id,
            'title' => $book->title,
            'year'    => (int) $book->yr,
            'links'   => [
                [
                    'rel' => 'self',
                    'uri' => '/books/'.$book->id,
                ]
            ],
        ];
    }

    /**
     * Include Author
     *
     * @return League\Fractal\ItemResource
     */
    public function includeAuthor(Book $book)
    {
        $author = $book->author;

        return $this->item($author, new AuthorTransformer);
    }
}
~~~

These includes will be available but can never be requested unless the `Manager::parseIncludes()` method is
called:

~~~ php
<?php
use League\Fractal;

$fractal = new Fractal\Manager();

if (isset($_GET['include'])) {
    $fractal->parseIncludes($_GET['include']);
}

~~~

With this set, include can do some great stuff. If a client application were to call the URL `/books?include=author` then they would see author data in the
response.

These includes can be nested with dot notation too, to include resources within other resources.

**E.g:** `/books?include=author,publishers.somethingelse`

Note: `publishers` will also be included with `somethingelse` nested under it. This is shorthand for `publishers,publishers.somethingelse`.

This can be done to a limit of 10 levels. To increase or decrease the level of embedding here, use the `Manager::setRecursionLimit(5)`
method with any number you like, to strip it to that many levels. Maybe 4 or 5 would be a smart number, depending on the API.

### Default Includes

Just like with optional includes, default includes are defined in a property on the transformer:

~~~ php
<?php namespace App\Transformer;

use Acme\Model\Book;
use League\Fractal\TransformerAbstract;

class BookTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        'author'
    ];

    // ....

    /**
     * Include Author
     *
     * @param Book $book
     * @return \League\Fractal\Resource\Item
     */
    public function includeAuthor(Book $book)
    {
        $author = $book->author;

        return $this->item($author, new AuthorTransformer);
    }
}
~~~

This will look identical in output as if the user requested `?include=author`.

### Include Parameters

When including other resources, syntax can be used to provide extra parameters to the include
methods. These parameters are constructed in the URL, `?include=comments:limit(5|1):order(created_at|desc)`.

This syntax will be parsed and made available through a `League\Fractal\ParamBag` object, passed into the
include method as the second argument.

~~~ php
<?php

use League\Fractal\ParamBag;

    // ... transformer stuff ...

    private $validParams = ['limit', 'order'];

    /**
     * Include Comments
     *
     * @param Book $book
     * @param \League\Fractal\ParamBag
     * @return \League\Fractal\Resource\Item
     */
    public function includeComments(Book $book, ParamBag $params)
    {
    	// Optional params validation
        $usedParams = array_keys(iterator_to_array($params));
        if ($invalidParams = array_diff($usedParams, $this->validParams)) {
            throw new \Exception(sprintf('Invalid param(s): "%s". Valid param(s): "%s"', implode(',', $usedParams), implode(',', $this->validParams)));
        }

    	// Processing
        list($limit, $offset) = $params->get('limit');
        list($orderCol, $orderBy) = $params->get('order');

        $comments = $book->comments
            ->take($limit)
            ->skip($offset)
            ->orderBy($orderCol, $orderBy)
            ->get();

        return $this->collection($comments, new CommentTransformer);
    }
~~~

Parameters have a name, then multiple values which are always returned as an array, even if there is only one.
They are accessed by the `get()` method, but array access is also an option, so `$params->get('limit')` and
`$params['limit']` do the same thing.

### Eager-Loading vrs Lazy-Loading

This above examples happen to be using the lazy-loading functionality of an ORM for `$book->author`. Lazy-Loading
can be notoriously slow, as each time one item is transformered, it would have to go off and find other data leading to a
huge number of SQL requests.

Eager-Loading could easily be used by inspecting the value of `$_GET['include']`, and using that to produce a
list of relationships to eager-load with an ORM.

