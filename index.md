---
layout: default
permalink: /
title: Introduction
---

# Introduction

[![Author](http://img.shields.io/badge/author-@philsturgeon-blue.svg?style=flat-square)](https://twitter.com/philsturgeon)
[![Source Code](http://img.shields.io/badge/source-league/fractal-blue.svg?style=flat-square)](https://github.com/thephpleague/fractal)
[![Latest Version](https://img.shields.io/github/release/thephpleague/fractal.svg?style=flat-square)](https://github.com/thephpleague/fractal/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/thephpleague/fractal/blob/master/LICENSE)<br />
[![Build Status](https://img.shields.io/travis/thephpleague/fractal/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/fractal)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/fractal.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/fractal/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/fractal.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/fractal)
[![Total Downloads](https://img.shields.io/packagist/dt/league/fractal.svg?style=flat-square)](https://packagist.org/packages/league/fractal)

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
Fractal is maintained by Graham Daniels and Jason Lewis. They can be found on Twitter at [@greydnls](https://twitter.com/greydnls) and [@jasonclewis](https://twitter.com/jasonclewis).
