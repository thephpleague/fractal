---
layout: default
permalink: simple-example/
title: Simple example
---

Simple Example
==============

For the sake of simplicity, this example has been put together as though it was
one file. In reality you would spread the manager initiation, data collection
and JSON conversion into separate parts of your application.

~~~ php
<?php
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

// Create a top level instance somewhere
$fractal = new Manager();

// Get data from some sort of source
// Most PHP extensions for SQL engines return everything as a string, historically
// for performance reasons. We will fix this later, but this array represents that.
$books = [
	[
		'id' => "1",
		'title' => "Hogfather",
		'yr' => "1998",
		'author_name' => 'Philip K Dick',
		'author_email' => 'philip@example.org',
	],
	[
		'id' => "2",
		'title' => "Game Of Kill Everyone",
		'yr' => "2014",
		'author_name' => 'George R. R. Satan',
		'author_email' => 'george@example.org',
	]
];

// Pass this array (collection) into a resource, which will also have a "Transformer"
// This "Transformer" can be a callback or a new instance of a Transformer object
// We type hint for array, because each item in the $books var is an array
$resource = new Collection($books, function(array $book) {
    return [
        'id'      => (int) $book['id'],
        'title'   => $book['title'],
        'year'    => (int) $book['yr'],
        'author'  => [
        	'name'  => $book['author_name'],
        	'email' => $book['author_email'],
        ],
        'links'   => [
            [
                'rel' => 'self',
                'uri' => '/books/'.$book['id'],
            ]
        ]
    ];
});

// Turn that into a structured array (handy for XML views or auto-YAML converting)
$array = $fractal->createData($resource)->toArray();

// Turn all of that into a JSON string
echo $fractal->createData($resource)->toJson();

// Outputs: {"data":[{"id":1,"title":"Hogfather","year":1998,"author":{"name":"Philip K Dick","email":"philip@example.org"}},{"id":2,"title":"Game Of Kill Everyone","year":2014,"author":{"name":"George R. R. Satan","email":"george@example.org"}}]}
~~~

It is worth noting that callbacks are a fairly shoddy replacement for using real
[Transformers](/transformers). They allow you to reuse transformers and keep your
controllers lightweight.
