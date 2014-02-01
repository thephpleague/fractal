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
