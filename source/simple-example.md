---
layout: layout
title: Simple example
---

Simple Example
==============

For the sake of simplicity, this example has been put together as though it was 
one file. In reality you would spread the manager initiation, data collection 
and JSON conversion into seperate parts of your application.

~~~.language-php
<?php
use League\Fractal;

// Create a top level instance somewhere
$fractal = new Fractal\Manager();
$fractal->setRequestedScopes(explode(',', $_GET['embed']));

// Get data from some sort of source
// Most PHP extensions for SQL engines return everything as a string for... speed?
$books = [
	[
		'id' => "1",
		'title' => "Hogfather",
		'year' => "1998",
		'author_email' => 'philip@example.com'
	],
	[
		'id' => "2",
		'title' => "Going Postal",
		'year' => "2004",
		'author_email' => 'philip@example.com'
	]
];

// Pass this array (collection) into a resource, which will also have a "Transformer"
// This "Transformer" can be a callback or a new instance of a Transformer object
// We type hint for array, because each item in the $books var is an array
$resource = new Fractal\Resource\Collection($books, function(array $book) {
    return [
        'id' => (int) $book['id'],
        'title' => $book['title'],
        'year' => $book['yr'],
    ];
});

// Turn all of that into JSON
$json = $fractal->createData($resource)->toJson();

echo $json;
~~~

It's also worth noting that callbacks are a fairly shoddy replacement for using
real [Transformers](/transformers).