---
layout: layout
---

# Fractal

[![Build Status](https://travis-ci.org/thephpleague/fractal.svg?branch=master)](https://travis-ci.org/thephpleague/fractal)
[![Latest Stable Version](https://poser.pugx.org/league/fractal/v/stable.png)](https://packagist.org/packages/league/fractal)
[![Total Downloads](https://poser.pugx.org/league/fractal/downloads.png)](https://packagist.org/packages/league/fractal)
[![Coverage Status](https://coveralls.io/repos/thephpleague/fractal/badge.png)](https://coveralls.io/r/thephpleague/fractal)
[![License](http://img.shields.io/packagist/l/league/fractal.svg)](https://github.com/thephpleague/fractal/blob/master/LICENSE)

<ul class="quick_links">
    <li><a class="github" href="https://github.com/thephpleague/fractal">View Source</a></li>
    <li><a class="twitter" href="https://twitter.com/philsturgeon">Follow Author</a></li>
</ul>

## What is Fractal?

Fractal provides a presentation and transformation layer for complex data output, the like found in
RESTful APIs, and works really well with JSON. Think of this as a view layer for your JSON/YAML/etc.

When building an API it is common for people to just grab stuff from the database and pass it
to `json_encode()`. This might be passable for "trivial" APIs but if they are in use by the public,
or used by mobile applications then this will quickly lead to inconsistent output.

[Fractal on Packagist](https://packagist.org/packages/league/fractal)

## Goals

* Create a "barrier" between source data and output, so schema changes do not affect users
* Systematic type-casting of data, to avoid `foreach()`ing through and `(bool)`ing everything
* Include (a.k.a embedding, nesting or side-loading) relationships for complex data structures
* Work with standards like HAL and JSON-API but also allow custom serialization
* Support the pagination of data results, for small and large data sets alike
* Generally ease the subtle complexities of outputting data in a non-trivial API

## Questions?

Fractal was created by Phil Sturgeon. Find him on Twitter at [@philsturgeon](https://twitter.com/philsturgeon).


