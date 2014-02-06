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
        'id' => (int) $book->id,
        'title' => $book->title,
        'year' => $book->yr,
    ];
});
~~~

## Classes for Transformers

Using callbacks like the previous example makes code reuse difficult, leading to 
duplication of code. To reuse transformers (recommended) classes can be defined, 
instantiated and passed in place of the callback.

These classes must extend `League\Fractal\TransformerAbstract` and contain a method
with the name `transform()`. 

The method declaraton is free form and can take mixed input, just like the 
callbacks: 

~~~.language-php
namespace Acme\Transformer;

use Acme\Model\Book;
use League\Fractal;

class BookTransformer extends Fractal\TransformerAbstract
{
	public function transform(Book $book)
	{
	    return [
	        'id' => (int) $book->id,
	        'title' => $book->title,
	        'year' => $book->yr,
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


## Embedding Data

Your transformer at this point is mainly just giving you a method to handle array conversion from
you data source (or whatever your model is returning) to a simple array. Embedding data in an
intelligent way can be tricky as data can have all sorts of relationships. Many developers try to
find a perfect balance between not making too many HTTP requests and not downloading more data than
they need to, so flexibility is also important.

Sticking with the book example, the `BookTransformer` might contain an optional embed for an author.

~~~.language-php
<?php namespace App\Transformer;

use Acme\Model\Book;
use League\Fractal\TransformerAbstract;

class BookTransformer extends TransformerAbstract
{
    /**
     * List of resources possible to embed via this transformer
     *
     * @var array
     */
    protected $availableEmbeds = [
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
            'year'  => $book->yr,
        ];
    }

    /**
     * Embed Author
     *
     * @return League\Fractal\ItemResource
     */
    public function embedAuthor(Book $book)
    {
        $author = $book->author;

        return $this->item($author, new AuthorTransformer);
    }
}
~~~

So if a client application were to call the URL `/books?embed=author` then they would see author data in the
response. These can be nested with dot notation, as far as you like.

**E.g:** `/books?embed=author,publishers,publishers.somethingelse`

This example happens to be using the lazy-loading functionality of an ORM for `$book->author`, but there is no
reason that eager-loading could not also be used by inspecting the `$_GET['embed']` list of requested scopes. This would just be a translation array, turning scopes into eager-loading requirements.
