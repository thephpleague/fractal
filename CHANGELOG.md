## 0.10.0 (2014-10-17)

Features:

  - Added `ParamBag` to replace the array passsed to includes. It implements array access so keep using it as you were, or play with the new methods.

Bugs:

  - Removed PaginatorInterface::getPaginator() as it was used anymore. [Issue #101]
  - Manager::createData() argument 1 now hints against `ResourceInterface` not `ResourceAbstract`.

[Issue #101]: https://github.com/thephpleague/fractal/issues/101

## 0.9.1 (2014-07-06)

Bugs:

  - Using ArraySerializer without a resource key would lead to an empty string as a key in JSON. [Issue #78]

[Issue #78]: https://github.com/thephpleague/fractal/issues/78

## 0.9.0 (2014-07-06)

Features:

  - Implemented serializer methods for item and collection separately [Issue #71]

[Issue #71]: https://github.com/thephpleague/fractal/issues/71

## 0.8.3 (2014-06-14)

Features:

  - Default Includes no longer need to be in Available Includes. [Issue #58]

[Issue #58]: https://github.com/thephpleague/fractal/issues/58

## 0.8.2 (2014-06-09)

Bugs:

  - A `null` value for `Manager::parseIncludes()` could have weird results

## 0.8.1 (2014-06-05)

Features:

  - Make `ResourceAbstract` implement `ResourceInterface`

Bugs:

  - Fixed tests for Laravel 4.2 usage


## 0.8.0 (2014-05-27)

Features:

  - Added Serializers with ArraySerializer, DataArraySerializer (default) and a provisional JsonApiSerializer. See [Issue #47]
  - Added `ResourceAbstract::setMeta('foo', mixed)` to allow custom meta data
  - Replaced `Manager::setRequestedScopes()` with `Manager::parseIncludes('foo,bar')` which can be an array or CSV string. It can
  also take "Smart Syntax" such as `Manager::parseIncludes('bars:limit(5|1):order(-something)')`, which can come from a URL query
  param: `/foo?include=bars:limit(5|1):order(-something)`
  - Made all pagination (paginators and cursors) use meta output logic, so it sits with your custom meta data
  - Moved `League\Fractal\Cursor\Cursor` and `League\Fractal\Cursor\CursorInterface` into `League\Fractal\Pagination`

[Issue #27]: https://github.com/thephpleague/fractal/issues/27
[Issue #47]: https://github.com/thephpleague/fractal/pull/47

## 0.7.0 (2014-02-01)

Features:

  - Added Cursor, as a different approach to paginating large data sets
  - Switched from PSR-0 to PSR-4

## 0.6.0 (2013-12-27)

Features:

  - Adds a `PaginatorInterface`, with a `IlluminatePaginatorAdapter` to let Fractal be framework agnostic

## 0.5.1 (2013-12-13)

Bugs:

  - Fixed PHP 5.3 support. Short array syntax will be the death of me

## 0.5.0 (2013-12-13)

Features:

  - Removed `PaginatedCollection` and added `Collection::setPaginator()` instead.

## 0.4.6 (2013-12-13)

Features:

  - Allow $defaultEmbed to be enabled in a transformer, to always embed without requesting
