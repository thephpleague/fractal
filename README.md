# League Skeleton

[![Build Status](https://travis-ci.org/php-loep/fractal.png?branch=master)](https://travis-ci.org/php-loep/fractal)
[![Total Downloads](https://poser.pugx.org/league/fractal/downloads.png)](https://packagist.org/packages/league/fractal)
[![Latest Stable Version](https://poser.pugx.org/league/fractal/v/stable.png)](https://packagist.org/packages/league/fractal)

When building an API one of the most overlooked aspects is outputting the data. Most folks pass it straight off 
to `json_encode()` which is fine for trivial APIs but if they are in use by the public, or you have an iPhone
application you definitey cannot just go around changing your output willy nilly.

That, and the fact that embedding data is hard, especially in a way that is both flexbile and performant. This 
will let you do both.


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

use League\Fractal;

``` php
// Create a top level instance somewhere
$fractal = new Fractal\ResourceManager();
$fractal->setRequestedScopes(explode(',', $_GET['embed']));

// Create instances of a resource, either Item, Collection or Paginator and give it a callback
$resource = new Fractal\CollectionResource($categories, function($scope, $category) {
    return [
        'foo' => (int) $category->id,
        'bar' => $category->other_field
    ];
});

// You can use a different class and give it a process() method instead of using a callback
$resource = new Fractal\ItemResource($categories[0], 'Acme\Processor\CategoryProcessor');
$resource = new Fractal\CollectionResource($categories, 'Acme\Processor\CategoryProcessor');
$resource = new Fractal\PaginatorResource($categories, 'Acme\Processor\CategoryProcessor');

// When ready create your root scope
$data = $fractal->createData($resource)->toArray();

// Get the kettle on, you're done
echo json_encode($data);

```

So... I pass data in and then pass it back out? Wut?

The point of these processors is they can do the following:

* Type-cast your data, so not all of your booleans look like `"0"`
* Avoid db schema changes destroying changing your output
* Allow for unlimited nesting of data, using the following example in your processor:

``` php
    if ($scope->isRequested('category')) {
        $resource = new ItemResource($opp->category, CategoryProcessor::class);
        $data['category'] = $scope->embedChildScope('category', $resource);
    }
```

It may not yet be instantly obvious how awesome this is, but it will be when documentation exists.

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
