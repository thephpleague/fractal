---
layout: layout
title: Transformers
---

# Transformers

In the [Resources](/resources/) section the examples show off callbacks for 
transformers, but these are of limited use:

~~~.language-php
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

The method declaraton can take mixed input, just like the callbacks: 

~~~.language-php
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

~~~.language-php
use Acme\Transformer\BookTransformer;
use League\Fractal;

$resource = new Fractal\Resource\Item($book, new BookTransformer);
$resource = new Fractal\Resource\Collection($books, new BookTransformer);
~~~


## Including Data

Your transformer at this point is mainly just giving you a method to handle array conversion from
you data source (or whatever your model is returning) to a simple array. Including data in an
intelligent way can be tricky as data can have all sorts of relationships. Many developers try to
find a perfect balance between not making too many HTTP requests and not downloading more data than
they need to, so flexibility is also important.

Sticking with the book example, the `BookTransformer`, we might want to normalize our database and take
the two `author_*` fields out and put them in their own table. This include can be optional to reduce the size of the JSON response and is defined like so:

~~~.language-php
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

~~~.language-php
use League\Fractal;

$fractal = new Fractal\Manager();

if (isset($_GET['include'])) {
    $fractal->parseIncludes($_GET['include']);
}

~~~

With this set, include can do some great stuff. If a client application were to call the URL `/books?include=author` then they would see author data in the
response. 

These includes can be nested with dot notation too, to include resources within other resources. 

**E.g:** `/books?include=author,publishers,publishers.somethingelse`

This can be done to a limit of 10 levels. To increase or decrease the level of embedding here, use the `Manager::setRecursionLimit(5)` 
method with any number you like, to strip it to that many levels. Maybe 4 or 5 would be a smart number, depending on the API.

### Default Includes

Just like with optional includes, default includes are defined in a property on the transformer:

~~~.language-php
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
     * @return League\Fractal\ItemResource
     */
    public function includeAuthor(Book $book)
    {
        $author = $book->author;

        return $this->item($author, new AuthorTransformer);
    }
}
~~~

This will look identical in output as if the user requested `?include=author`.

### Eager-Loading vrs Lazy-Loading

This above examples happen to be using the lazy-loading functionality of an ORM for `$book->author`. Lazy-Loading
can be notoriously slow, as each time one item is transformered, it would have to go off and find other data leading to a 
huge number of SQL requests.

Eager-Loading could easily be used by inspecting the value of `$_GET['include']`, and using that to produce a 
list of relationships to eager-load with an ORM.

