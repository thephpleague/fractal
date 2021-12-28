# Fractal

[![Latest Version](https://img.shields.io/github/release/thephpleague/fractal.svg?style=flat-square)](https://github.com/thephpleague/fractal/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
![The PHP League Tests](https://github.com/thephpleague/fractal/workflows/The%20PHP%20League%20Tests/badge.svg)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/fractal.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/fractal/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/fractal.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/fractal)
[![Total Downloads](https://img.shields.io/packagist/dt/league/fractal.svg?style=flat-square)](https://packagist.org/packages/league/fractal)

Fractal provides a presentation and transformation layer for complex data output, the like found in
RESTful APIs, and works really well with JSON. Think of this as a view layer for your JSON/YAML/etc.

When building an API it is common for people to just grab stuff from the database and pass it
to `json_encode()`. This might be passable for "trivial" APIs but if they are in use by the public,
or used by mobile applications then this will quickly lead to inconsistent output.

## Goals

* Create a protective shield between source data and output, so schema changes do not affect users
* Systematic type-casting of data, to avoid `foreach()`ing through and `(bool)`ing everything
* Include (a.k.a embedding, nesting or side-loading) relationships for complex data structures
* Work with standards like HAL and JSON-API but also allow custom serialization
* Support the pagination of data results, for small and large data sets alike
* Generally ease the subtle complexities of outputting data in a non-trivial API

This package is compliant with [PSR-1], [PSR-2] and [PSR-4]. If you notice compliance oversights,
please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md


## Install

Via Composer

``` bash
$ composer require league/fractal
```

## Requirements

The following versions of PHP are supported by this version.

* PHP 7.0
* PHP 7.1
* PHP 7.2
* PHP 8.0
* PHP 8.1
* HHVM

## Documentation

Fractal has [full documentation](http://fractal.thephpleague.com), powered by [Jekyll](http://jekyllrb.com/).

Contribute to this documentation in the [gh-pages branch](https://github.com/thephpleague/fractal/tree/gh-pages/).

## Todo

- add HAL serializers

## Testing

``` bash
$ phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/thephpleague/fractal/blob/master/CONTRIBUTING.md) and [CONDUCT](https://github.com/thephpleague/fractal/blob/master/CONDUCT.md) for details.


## Maintainers

- [Korvin Szanto](https://github.com/korvinszanto)
- [Matt Trask](https://github.com/matthewtrask)

## Credits

- [Graham Daniels](https://github.com/greydnls)
- [Andrew Willis](https://github.com/willishq)
- [Phil Sturgeon](https://github.com/philsturgeon)
- [All Contributors](https://github.com/thephpleague/fractal/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/fractal/blob/master/LICENSE) for more information.
