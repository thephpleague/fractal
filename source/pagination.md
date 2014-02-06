---
layout: layout
title: Pagination
---

# Pagination

When working with a large data set it obviously makes sense to offer pagination options to the endpoint,
otherwise that data can get very slow. To avoid writing your own pagination output into every endpoint, 
Fractal provides you with two solutions:

* Paginator
* Cursor

## Using Paginators

Paginators offer more information about your result-set including total, and have next/previous links 
which will only show if there is more data available. This intelligence comes at the cost of having to 
count the number of entries in a database on each call. 

For some data sets this might not be an issue, but for some it certainly will. If pure speed is an issue, 
consder using Cursors instead.

Paginator objects are created, and must implement `League\Fractal\Pagination\PaginatorInterface`
and it's specified methods. The instantiated object must then be passed to the `League\Fractal\Resource\Collection::setPaginator()` method.

Fractal currently only ships with an adapter for Laravel's `illuminate/pagination` package as
`League\Fractal\Pagination\IlluminatePaginatorAdapter`, but more may be added at some point.

[Laravel Pagination]: http://laravel.com/docs/pagination

Inside of Laravel 4, using the Eloquent or Query Builder method `paginate()`, the following syntax is
possible:

~~~.language-php
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Acme\Model\Book;
use Acme\Transformer\BookTransformer;

$paginator = Book::paginate();
$books = $books->getCollection();

$resource = new Collection($books, new BookTransformer);
$resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
~~~

## Using Cursors

When we have large sets of data and running a `SELECT COUNT(*) FROM whatever` isn't really an option, we need a proper
way of fetching results. One of the approches is to use cursors that will indicate to your backend where to start
fetching results. You can set a new cursor on your collections using the
`League\Fractal\Resource\Collection::setCursor()` method.

The cursor must implement `League\Fractal\Cursor\CursorInterface` and it's specified methods.

Fractal currently ships with a very basic adapter: `League\Fractal\Cursor\Cursor`. It's really easy to use:

~~~.language-php
use Acme\Model\Book;
use Acme\Transformer\BookTransformer;
use League\Fractal\Cursor\Cursor;
use League\Fractal\Resource\Collection;

$books = new Book;

if ($current = Input::get('cursor', false)) {
    $books = $books->where('id', '>', $current);
}

$books = $books->take(5)->get();

$cursor = new Cursor($current, $books->last()->id, $books->count());

$resource = new Collection($books, new BookTransformer);
$resource->setCursor($cursor);
~~~