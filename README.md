# League Skeleton

[![Build Status](https://travis-ci.org/php-loep/fractal.png?branch=master)](https://travis-ci.org/php-loep/fractal)
[![Total Downloads](https://poser.pugx.org/league/fractal/downloads.png)](https://packagist.org/packages/league/fractal)
[![Latest Stable Version](https://poser.pugx.org/league/fractal/v/stable.png)](https://packagist.org/packages/league/fractal)
[![Latest Unstable Version](https://poser.pugx.org/league/fractal/v/unstable.png)](https://packagist.org/packages/league/fractal)

When building an API one of the most overlooked aspects is outputting the data. Most folks just grab stuff 
from the database and pass it straight off to `json_encode()` which is fine for trivial APIs but if they are 
in use by the public, or you have an iPhone application you definitey cannot just go around changing your 
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
        "league/fractal": "dev-master"
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

In your controllers you can then create "resources", of which there are three types:

* **League\Fractal\ItemResource** - A singular resource, probably one entry in a data store
* **League\Fractal\CollectionResource** - A collection of resources
* **League\Fractal\PaginatorResource** - A collection of resources, but also supports pagination. This 
only accepts an instance of `Illuminate\Pagination\Paginator` at this point

The `ItemResource` and `CollectionResource` constructors will take any kind of data you wish to send it 
as the first argument, and then a "processor" as the second argument. This can be callable or a string 
containing a fully-qualified class name. The processor will have to arguments passed to it, which will 
be a `$scope` (explained later) and an iteration of your data.

So if you passed an instance of `BookModel` into an `ItemResource` then you can expect this instance to 
be `BookModel`. If you passed an array or a collection (an object implementing [ArrayIterator][]) 
of `BookModel` instances then this process method will be run on each of those instances.

``` php
$resource = new Fractal\CollectionResource($categories, function($scope, BookModel $book) {
    return [
        'foo' => (int) $book->id,
        'bar' => $book->other_field
    ];
});
```

If you want to reuse your processors (recommended) then create classes somewhere and pass in the name.
Assuming you use an autoloader of course. These classes must extend `League\Fractal\ProcessorAbstract` and 
contain a process method, much like the callback example: `public function process($scope, Foo $foo)`.

``` php
$resource = new Fractal\ItemResource($categories[0], 'Acme\Processor\CategoryProcessor');
$resource = new Fractal\CollectionResource($categories, 'Acme\Processor\CategoryProcessor');
$resource = new Fractal\PaginatorResource($categories, 'Acme\Processor\CategoryProcessor');
```

[ArrayIterator]: http://php.net/ArrayIterator

### Embedding (a.k.a Nesting) Data

Your processor at this point is mainly just giving you a method to handle array conversion from 
youe custom data format to a simple array. Embedding data in an intelligent way can be tricky as 
data can have all sorts of relationships. Many developers try to find a perfect balance between 
not making too many HTTP requests and not downloading more data than they need to, so flexibility 
us also important. 

If we were in a 

``` php
    if ($scope->isRequested('category')) {
        $resource = new Fractal\ItemResource($opp->category, CategoryProcessor::class);
        $data['category'] = $scope->embedChildScope('category', $resource);
    }
```

### Outputting Processed Data

When ready to output this data, you must convert the "resource" back into data. Calling 
`$fractal->createData();` with a resource argument will run the processors (any any 
nested processor calls) and convert everything to an array for you to output:

``` php
$data = $fractal->createData($resource)->toArray();

echo json_encode($data);
```

Grab a beverage, you're done. If you want to use something other than JSON then you'll need to 
think that one up yourself. If you're using horribly complicated XML for example, then you will 
probably need to create some specific view files, which negates the purpose of using this system 
entirely. Auto-generated XML, YAML or anything similar could easily be set up in a switch, just 
check against the `Accept` header.


## TODO

This is still in concept stage, and these issues are left to explore:

- [ ] Discuss the class names and file structure with others
- [ ] Should Processors be called Presenters?
- [ ] Simplify the assosciation of nested items. Move to a register method? 
- [ ] Implement HATEOAS/HAL links. Suggestions welcome
- [ ] Support other pagination systems, not just `Illuminate\Pagination`.


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
