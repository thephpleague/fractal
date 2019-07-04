## Next

### Enhancements
- Add links for all available includes to `JsonApiSerializer` #331 - Thanks @matt-allan
- Implement interface `\JsonSerializable` in Scopes to allow for direct usage with `json_encode()`



## 0.18.0 - 2019-05-09

### Enhancements
- Allow PHPUnit 7+ where available - Thanks @ramsey
- Changed the paginator to not call `$paginator->getPaginate()` in `PhalconFrameworkPaginatorAdapter` constructor - Thanks @niden
- Output an object for `attributes` when none are provided instead of an array in `JsonApiSerializer` - Thanks @BastianHofmann
- Include `meta` key for relationships in `JsonApiSerializer` - Thanks @rawaludin
- Allow `self` link to override defaults in `JsonApiSerializer` - Thanks @biggator
- Preserve numeric keys when converting `Scope`s to arrays by using array union instead of array_merge - Thanks @andrewtweber
- Output an object for `links` when none are provided instead of an array in `ArraySerializer` pagination - Thanks @basavind
- Allow arrays for field lists in `$fractal->parseFieldsets()` - Thanks @flugger


## 0.17.0 - 2017-08-18

### Enhancements

- Add ScopeFactoryInterface and ScopeFactory
- Add $options parameter to toJson
- Added Serializer interface to SerializerAbstract
- Add Primitive resource type, allows to use primitives
- optimized primitive transformation when transformer is not given



## 0.16.0 - 2017-03-12

### Enhancements

- #358 Allow the ability to define meta on each object in collection
- #362 Add DoctrinePaginatorAdapter to interface with Doctrine pagination
- Implement sparse fieldset

## 0.15.0 - 2016-12-28

### Enhancements

- Custom links in serializer
- PhalconPHP Paginator
- tested on 7.1

## 0.14.0 - 2016-07-21

### Added

- `null()` method for serializing null resource.
- Adds pagination to `JsonApiSerializer`
- Added JSON-API serializer collection links

### Enhancements

- Fix issue including relationships on collections
- `TransformerAbstract` now returns a `ResourceInterface`
- `getIncludeParams` now always return `ParamBag`
- cody styling, method naming and docs

## 0.13.0 - 2015-10-16

### Added

- `->setData(...)` and `->setTransformer(...)` methods to resources
- able to set url include parameters with no set variables, ie. `include=comments:limit(5|1):new`

### Changed

- Finally, much improved JSON-API support thanks to @felixkiss

### Enhancements

- Short array syntax throughout
- Test improvements

## 0.12.0 - 2015-03-19

### Added

- Added PHP 7.0-dev support.
- Add the support for Laravel 5 paginator. [Issue #153]

### Changed

- Now accessing the default and available includes in Transformers via getters [Issue #158]

### Removed

- Dropped PHP 5.3 support. Use [v0.11] if you're still stuck on PHP 5.3.
- Removed `getData()` and `getTransformer()` from `League\Fractal\Resource\Collection` as they were already
  defined in `ResourceAbstract`.

[Issue #153]: https://github.com/thephpleague/fractal/pull/153
[Issue #158]: https://github.com/thephpleague/fractal/pull/158
[v0.11]: https://github.com/thephpleague/fractal/releases/tag/0.11.0

## 0.11.0 - 2014-12-10

### Added

- Added `League\Fractal\Pagination\PagerfantaPaginatorAdapter` to support [Pagerfanta]
- Added `League\Fractal\Pagination\ZendFrameworkPaginatorAdapter` to support [Zend Framework Paginator]

### Fixed

- Now JSON-API linked items will be unique based on their ID [Issue #126]

[Pagerfanta]: https://packagist.org/packages/pagerfanta/pagerfanta
[Zend Framework Paginator]: https://packagist.org/packages/zendframework/zend-paginator
[Issue #126]: https://github.com/thephpleague/fractal/issues/126

## 0.10.0 - 2014-10-17

### Added

- Added `ParamBag` to replace the array passed to includes. It implements array access so keep using it as you were, or play with the new methods.

### Fixed

- Removed `PaginatorInterface::getPaginator()` as it was used anymore. [Issue #101]
- `Manager::createData()` argument 1 now hints against `ResourceInterface` not `ResourceAbstract`.

[Issue #101]: https://github.com/thephpleague/fractal/issues/101

## 0.9.1 - 2014-07-06

### Fixed

- Using ArraySerializer without a resource key would lead to an empty string as a key in JSON. [Issue #78]

[Issue #78]: https://github.com/thephpleague/fractal/issues/78

## 0.9.0 - 2014-07-06

### Added

- Implemented serializer methods for item and collection separately [Issue #71]

[Issue #71]: https://github.com/thephpleague/fractal/issues/71

## 0.8.3 - 2014-06-14

### Added

- Default Includes no longer need to be in Available Includes. [Issue #58]

[Issue #58]: https://github.com/thephpleague/fractal/issues/58

## 0.8.2 - 2014-06-09

### Fixed

- A `null` value for `Manager::parseIncludes()` could have weird results

## 0.8.1 - 2014-06-05

### Added

- Make `ResourceAbstract` implement `ResourceInterface`

### Fixed

- Fixed tests for Laravel 4.2 usage


## 0.8.0 - 2014-05-27

### Added

- Added Serializers with ArraySerializer, DataArraySerializer (default) and a provisional JsonApiSerializer. See [Issue #47]
- Added `ResourceAbstract::setMeta('foo', mixed)` to allow custom meta data
- Replaced `Manager::setRequestedScopes()` with `Manager::parseIncludes('foo,bar')` which can be an array or CSV string. It can also take "Smart Syntax" such as `Manager::parseIncludes('bars:limit(5|1):order(-something)')`, which can come from a URL query param: `/foo?include=bars:limit(5|1):order(-something)`
- Made all pagination (paginators and cursors) use meta output logic, so it sits with your custom meta data
- Moved `League\Fractal\Cursor\Cursor` and `League\Fractal\Cursor\CursorInterface` into `League\Fractal\Pagination`

[Issue #27]: https://github.com/thephpleague/fractal/issues/27
[Issue #47]: https://github.com/thephpleague/fractal/pull/47

## 0.7.0 - 2014-02-01

### Added

- Added Cursor, as a different approach to paginating large data sets
- Switched from PSR-0 to PSR-4

## 0.6.0 - 2013-12-27

### Added

- Adds a `PaginatorInterface`, with a `IlluminatePaginatorAdapter` to let Fractal be framework agnostic

## 0.5.1 - 2013-12-13

### Fixed

- Fixed PHP 5.3 support. Short array syntax will be the death of me

## 0.5.0 - 2013-12-13

### Added

- Added `Collection::setPaginator()`.

### Removed

- Removed `PaginatedCollection`, use `Collection::setPaginator()` instead.

## 0.4.6 - 2013-12-13

### Added

- Allow `$defaultEmbed` to be enabled in a transformer, to always embed without requesting
