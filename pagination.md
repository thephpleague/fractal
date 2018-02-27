---
layout: default
permalink: pagination/
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
consider using Cursors instead.

Paginator objects are created, and must implement `League\Fractal\Pagination\PaginatorInterface`
and its specified methods. The instantiated object must then be passed to the `League\Fractal\Resource\Collection::setPaginator()` method.

Fractal currently ships with the following adapters:

* Laravel's `illuminate/pagination` package as `League\Fractal\Pagination\IlluminatePaginatorAdapter`
* The `pagerfanta/pagerfanta` package as `League\Fractal\Pagination\PagerfantaPaginatorAdapter`
* Zend Framework's `zendframework/zend-paginator` package as `League\Fractal\Pagination\ZendFrameworkPaginatorAdapter`

### Laravel Pagination

As an example, you can use Laravel's Eloquent or Query Builder method `paginate()` to achieve the following:

~~~ php
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Acme\Model\Book;
use Acme\Transformer\BookTransformer;

$paginator = Book::paginate();
$books = $paginator->getCollection();

$resource = new Collection($books, new BookTransformer);
$resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
~~~

### Symfony Pagination

Below is an example of pagination using the Pagerfanter Paginator with a collection of objects obtained from Doctrine.

~~~ php
$doctrineAdapter = new DoctrineCollectionAdapter($allItems);
$paginator = new Pagerfanta($doctrineAdapter);
$filteredResults = $paginator->getCurrentPageResults();

$paginatorAdapter = new PagerfantaPaginatorAdapter($paginator, function(int $page) use (Request $request, RouterInterface $router) {
	$route = $request->attributes->get('_route');
	$inputParams = $request->attributes->get('_route_params');
	$newParams = array_merge($inputParams, $request->query->all());
	$newParams['page'] = $page;
	return $router->generate($route, $newParams, 0);
});
$resource = new Collection($filteredResults, new BookTransformer);
$resource->setPaginator($paginatorAdapter);
~~~

#### Including existing query string values in pagination links

In the example above, previous and next pages will be provided simply with `?page=#` ignoring all other
existing query strings. To include all query string values automatically in these links we can replace
the last line above with:

~~~ php
use Acme\Model\Book;

$year = Input::get('year');
$paginator = Book::where('year', '=', $year)->paginate(20);

$queryParams = array_diff_key($_GET, array_flip(['page']));
$paginator->appends($queryParams);

$paginatorAdapter = new IlluminatePaginatorAdapter($paginator);
$resource->setPaginator($paginatorAdapter);
~~~

## Using Cursors

When we have large sets of data and running a `SELECT COUNT(*) FROM whatever` isn't really an option, we need a proper
way of fetching results. One of the approaches is to use cursors that will indicate to your backend where to start
fetching results. You can set a new cursor on your collections using the
`League\Fractal\Resource\Collection::setCursor()` method.

The cursor must implement `League\Fractal\Pagination\CursorInterface` and its specified methods.

Fractal currently ships with a very basic adapter: `League\Fractal\Pagination\Cursor`. It's really easy to use:

~~~ php
use Acme\Model\Book;
use Acme\Transformer\BookTransformer;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Collection;

$currentCursor = Input::get('cursor', null);
$previousCursor = Input::get('previous', null);
$limit = Input::get('limit', 10);

if ($currentCursor) {
    $books = Book::where('id', '>', $currentCursor)->take($limit)->get();
} else {
    $books = Book::take($limit)->get();
}

$newCursor = $books->last()->id;
$cursor = new Cursor($currentCursor, $previousCursor, $newCursor, $books->count());

$resource = new Collection($books, new BookTransformer);
$resource->setCursor($cursor);
~~~

These examples are for Laravel's `illuminate\database` package, but you can do it however you like. The cursor
also happens to be constructed from the `id` field, but it could just as easily be an offset number. Whatever
is picked to represent a cursor, maybe consider using `base64_encode()` and `base64_decode()` on the values to make sure
API users do not try and do anything too clever with them. They just need to pass the cursor to the new URL, not do any maths.

### Example Cursor Usage

**GET /books?cursor=5&limit=5**

~~~ json
{
	"books": [
		{ "id": 6 },
		{ "id": 7 },
		{ "id": 8 },
		{ "id": 9 },
		{ "id": 10 }
	],
	"meta": {
		"cursor": {
			"previous": null,
			"current": 5,
			"next": 10,
			"count": 5
		}
	}
}
~~~

On the next request, we move the cursor forward.

 * Set `cursor` to `next` from the last response
 * Set `previous` to `current` from the last response
 * `limit` is optional
 	* You can set it to `count` from the previous request to maintain the same limit

**GET /books?cursor=10&previous=5&limit=5**

~~~ json
{
	"books": [
		{ "id": 11 },
		{ "id": 12 },
		{ "id": 13 },
		{ "id": 14 },
		{ "id": 15 }
	],
	"meta": {
		"cursor": {
			"previous": 5,
			"current": 10,
			"next": 15,
			"count": 5
		}
	}
}

~~~
