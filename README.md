# Fractal

[![Build Status](https://travis-ci.org/thephpleague/fractal.png?branch=master)](https://travis-ci.org/thephpleague/fractal)
[![Coverage Status](https://coveralls.io/repos/thephpleague/fractal/badge.png)](https://coveralls.io/r/thephpleague/fractal)
[![Total Downloads](https://poser.pugx.org/league/fractal/downloads.png)](https://packagist.org/packages/league/fractal)
[![Latest Stable Version](https://poser.pugx.org/league/fractal/v/stable.png)](https://packagist.org/packages/league/fractal)

When building an API it is common for people to just grab stuff from the database and pass it
to `json_encode()`. This might be passable for "trivial" API's but if they are in use by the public,
or used by an iPhone application then this will quickly lead to inconsistent output.

This package aims to do a few simple things:

* Allow an area for you to type-cast your data, so not all of your booleans look like `"0"`
* Avoid db schema changes changing your output
* Allow for simple, flexible and controllable embedding of data, avoiding infinite loops

This package is compliant with [PSR-0][], [PSR-1][], and [PSR-2][]. If you
notice compliance oversights, please send a patch via pull request.

[PSR-0]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md


## Install

Via Composer

``` json
{
    "require": {
        "league/fractal": "0.6.*"
    }
}
```

## Usage

Shove this in your base controller or an IoC somehow.

``` php
use League\Fractal;

// Create a top level instance somewhere
$fractal = new Fractal\Manager();
$fractal->setRequestedScopes(explode(',', $_GET['embed']));
```

### Creating Resources and Transformers

In your controllers you can then create "resources", of which there are three types:

* **League\Fractal\Resource\Item** - A singular resource, probably one entry in a data store
* **League\Fractal\Resource\Collection** - A collection of resources

The `Item` and `Collection` constructors will take any kind of data you wish to send it
as the first argument, and then a "transformer" as the second argument. This can be callable or a string
containing a fully-qualified class name.

The transformer will the raw data passed back into it, so if you pass an instance of `BookModel` into an
`ItemResource` then you can expect this instance to be `BookModel`. If you passed an array or a collection
(an object implementing [ArrayIterator][]) of `BookModel` instances then this transform method will be run
on each of those instances.

``` php
use League\Fractal;

$resource = new Fractal\Resource\Collection($books, function(BookModel $book) {
    return [
        'id' => (int) $book->id,
        'title' => $book->title,
        'year' => $book->yr,
    ];
});
```

If you want to reuse your transformers (recommended) then create classes somewhere and pass in the name.
Assuming you use an autoloader of course. These classes must extend `League\Fractal\TransformerAbstract` and
contain a transform method, much like the callback example: `public function transform(Foo $foo)`.

``` php
use League\Fractal;
use Acme\Transformer\BookTransformer;

$resource = new Fractal\Resource\Item($book, new BookTransformer);
$resource = new Fractal\Resource\Collection($books, new BookTransformer);
```

### Embedding Data

Your transformer at this point is mainly just giving you a method to handle array conversion from
you data source (or whatever your model is returning) to a simple array. Embedding data in an
intelligent way can be tricky as data can have all sorts of relationships. Many developers try to
find a perfect balance between not making too many HTTP requests and not downloading more data than
they need to, so flexibility is also important.

Sticking with the book example, the `BookTransformer` might contain an optional embed for an author.

``` php
<?php namespace App\Transformer;

use Book;
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
```

So if a client application were to call the URL `/books?embed=author` then they would see author data in the
response. These can be nested with dot notation, as far as you like.

**E.g:** `/books?embed=author,publishers,publishers.somethingelse`

This example happens to be using the lazy-loading functionality of an ORM for `$book->author`, but there is no
reason that eager-loading could not also be used by inspecting the `$_GET['embed']` list of requested scopes. This
would just be a translation array, turning scopes into eager-loading requirements.

### Outputting Processed Data

When ready to output this data, you must convert the "resource" back into data. Calling
`$fractal->createData();` with a resource argument will run the transformers (any any
nested transformer calls) and convert everything to an array for you to output:

``` php
// Play with the array
$data = $fractal->createData($resource)->toArray();

// Straight to JSON
$json = $fractal->createData($resource)->toJson();
```

If you want to use something other than JSON then you'll need to think that one up yourself. If
you're using horribly complicated XML for example, then you will probably need to create some
specific view files, which negates the purpose of using this system entirely. Auto-generated XML,
YAML or anything similar could easily be set up in a switch, just check against the `Accept` header.

### Pagination

When working with a large data set it obviously makes sense to offer pagination options to the endpoint,
otherwise that data can get very slow. To avoid writing your own pagination output into every endpoint you
can utilize the the `League\Fractal\Resource\Collection::setPaginator()` method.

The paginator passed to `setPaginator()` must implement `League\Fractal\Pagination\PaginatorInterface`
and it's specified methods.

Fractal currently only ships with an adapter for Laravel's `illuminate/pagination` package as
`League\Fractal\Pagination\IlluminatePaginatorAdapter`.

[Laravel Pagination]: http://laravel.com/docs/pagination

Inside of Laravel 4, using the Eloquent or Query Builder method `paginate()`, the following syntax is
possible:

``` php
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Acme\Model\Book;
use Acme\Transformer\BookTransformer;

$paginator = Books::paginate();
$books = $books->getCollection();

$resource = new Collection($books, new BookTransformer);
$resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
```

[ArrayIterator]: http://php.net/ArrayIterator

### Navigate collections with cursors

When we have large sets of data and running a `SELECT COUNT(*) FROM whatever` isn't really an option, we need a proper
way of fetching results. One of the approches is to use cursors that will indicate to your backend where to start
fetching results. You can set a new cursor on your collections using the
`League\Fractal\Resource\Collection::setCursor()` method.

The cursor must implement `League\Fractal\Cursor\CursorInterface` and it's specified methods.

Fractal currently ships with a very basic adapter, `League\Fractal\Cursor\Cursor`. It's really easy to use:

```php
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
```

## TODO

This is still in concept stage, and these issues are left to explore:

- [ ] Switch return array to use instance properties in `transform()`
- [ ] Implement [HATEOAS](http://en.wikipedia.org/wiki/HATEOAS)/[HAL](http://stateless.co/hal_specification.html) links
- [ ] Add smart embed syntax, e.g: `?embed=foo:limit(5):order(something,asc)`

## Testing

``` bash
$ phpunit
```


## Contributing

Please see [CONTRIBUTING](https://github.com/thephpleague/fractal/blob/master/CONTRIBUTING.md) for details.


## Credits

- [Phil Sturgeon](https://github.com/philsturgeon)
- [All Contributors](https://github.com/thephpleague/fractal/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/fractal/blob/master/LICENSE) for more information.
