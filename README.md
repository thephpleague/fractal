# Fractal

[![Build Status](https://travis-ci.org/php-loep/fractal.png?branch=master)](https://travis-ci.org/php-loep/fractal)
[![Coverage Status](https://coveralls.io/repos/php-loep/fractal/badge.png)](https://coveralls.io/r/php-loep/fractal)
[![Total Downloads](https://poser.pugx.org/league/fractal/downloads.png)](https://packagist.org/packages/league/fractal)
[![Latest Stable Version](https://poser.pugx.org/league/fractal/v/stable.png)](https://packagist.org/packages/league/fractal)
[![Latest Unstable Version](https://poser.pugx.org/league/fractal/v/unstable.png)](https://packagist.org/packages/league/fractal)

When building an API one of the most overlooked aspects is outputting the data. Most folks just grab stuff 
from the database and pass it straight off to `json_encode()` which is fine for trivial APIs but if they are 
in use by the public, or you have an iPhone application you definitely cannot just go around changing your 
output willy nilly.

This package aims to do a few simple things:

* Allow an area for you to type-cast your data, so not all of your booleans look like `"0"`
* Avoid db schema changes changing your output
* Allow for simple, flexible and controllable embedding of data, avoiding infinite loops


## Install

Via Composer

``` json
{
    "require": {
        "league/fractal": "0.3.*"
    }
}
```

## Usage

Shove this in your base controller or an IoC somehow.

``` php
use League\Fractal;

// Create a top level instance somewhere
$fractal = new Fractal\ResourceManager();
$fractal->setRequestedScopes(explode(',', $_GET['embed']));
```

### Creating Resources and Transformers

In your controllers you can then create "resources", of which there are three types:

* **League\Fractal\ItemResource** - A singular resource, probably one entry in a data store
* **League\Fractal\CollectionResource** - A collection of resources
* **League\Fractal\PaginatorResource** - A collection of resources, but also supports pagination. This 
only accepts an instance of `Illuminate\Pagination\Paginator` at this point

The `ItemResource` and `CollectionResource` constructors will take any kind of data you wish to send it 
as the first argument, and then a "transformer" as the second argument. This can be callable or a string 
containing a fully-qualified class name. 

The transformer will the raw data passed back into it, so if you pass an instance of `BookModel` into an 
`ItemResource` then you can expect this instance to be `BookModel`. If you passed an array or a collection 
(an object implementing [ArrayIterator][]) of `BookModel` instances then this transform method will be run 
on each of those instances.

``` php
$resource = new Fractal\CollectionResource($books, function(BookModel $book) {
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
// PHP 5.3+
$resource = new Fractal\ItemResource($books[0], 'Acme\Transformer\BookTransformer');
$resource = new Fractal\CollectionResource($books, 'Acme\Transformer\BookTransformer');
$resource = new Fractal\PaginatorResource($books, 'Acme\Transformer\BookTransformer');

// Alternative for PHP 5.5+
use Acme\Transformer\BookTransformer;
$resource = new Fractal\ItemResource($books[0], BookTransformer::class);
$resource = new Fractal\CollectionResource($books, BookTransformer::class);
$resource = new Fractal\PaginatorResource($books, BookTransformer::class);

```

### Embedding (a.k.a Nesting) Data

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

        return $this->itemResource($author, AuthorTransformer::class);
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

Grab a beverage, you're done. If you want to use something other than JSON then you'll need to 
think that one up yourself. If you're using horribly complicated XML for example, then you will 
probably need to create some specific view files, which negates the purpose of using this system 
entirely. Auto-generated XML, YAML or anything similar could easily be set up in a switch, just 
check against the `Accept` header.


## TODO

This is still in concept stage, and these issues are left to explore:

- [X] Should Transformers be called Presenters? (Went with Transformers)
- [X] Simplify the assosciation of nested items. Move to a register method? 
- [ ] Switch return array to use instance properties in `transform()`
- [ ] Implement HATEOAS/HAL links
- [ ] Support other pagination systems, not just `Illuminate\Pagination`


## Testing

``` bash
$ phpunit
```


## Contributing

Please see [CONTRIBUTING](https://github.com/php-loep/fractal/blob/master/CONTRIBUTING.md) for details.


## Credits

- [Phil Sturgeon](https://github.com/philsturgeon)
- [All Contributors](https://github.com/php-loep/fractal/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/php-loep/fractal/blob/master/LICENSE) for more information.

[ArrayIterator]: http://php.net/ArrayIterator
