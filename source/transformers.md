---
layout: layout
title: Transformers
---

# Transformers

In the [Resources](/resources/) section the examples show off callbacks for 
transformers, but these are of limited use:

~~~.language-php
use League\Fractal;

$books = BookModel::all();

$resource = new Fractal\Resource\Collection($books, function(BookModel $book) {
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

use BookModel;
use League\Fractal;

class BookTransformer extends Fractal\TransformerAbstract
{
	public function transform(BookModel $book)
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
