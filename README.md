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

This package is compliant with [PSR-1][], [PSR-2][] and [PSR-4][]. If you
notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md


## Install

Via Composer

``` json
{
    "require": {
        "league/fractal": "0.7.*"
    }
}
```

## Documentation

Read [full documentation](http://fractal.thephpleague.com) here.

## Todo

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
