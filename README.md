# Fractal [![Build Status](https://travis-ci.org/thephpleague/fractal.png?branch=master)](https://travis-ci.org/thephpleague/fractal)

[![License](http://img.shields.io/packagist/l/league/fractal.svg)](https://github.com/thephpleague/fractal/blob/master/LICENSE)
[![Coverage Status](https://coveralls.io/repos/thephpleague/fractal/badge.png)](https://coveralls.io/r/thephpleague/fractal)
[![Total Downloads](https://poser.pugx.org/league/fractal/downloads.png)](https://packagist.org/packages/league/fractal)
[![Latest Stable Version](https://poser.pugx.org/league/fractal/v/stable.png)](https://packagist.org/packages/league/fractal)

Fractal provides a presentation and transformation layer for complex data output, the like found in 
RESTful APIs, and works really well with JSON.

When building an API it is common for people to just grab stuff from the database and pass it
to `json_encode()`. This might be passable for "trivial" API's but if they are in use by the public,
or used by an iPhone application then this will quickly lead to inconsistent output.

## Goals 

* Create a "barrier" between source data and output, so schema changes do not affect users
* Systematic type-casting of data, to avoid foreach()ing through and (bool)ing everything
* Embed (or nest) relationships for complex data structures
* Support the pagination of data results, for small and large data sets alike
* Generally ease the subtle complexities of outputting data in a non-trivial API

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

## Requirements

The following versions of PHP are supported by this version. 

* PHP 5.3
* PHP 5.4
* PHP 5.5
* PHP 5.6

HHVM support is also experimentally supported. It basically works, but CodeSniffer installation is screwing it up on 
Travis-CI.

## Documentation

Fractal has [full documentation](http://fractal.thephpleague.com), powered by [Sculpin](https://sculpin.io).

Contribute to this documentation in the [sculpin branch](https://github.com/thephpleague/fractal/tree/sculpin/source).

## Todo

- Wrap optional params in a ParamBag object or similar
- Complete JSON-API and HAL serializers

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
