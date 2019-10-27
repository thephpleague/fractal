---
layout: default
permalink: serializers/
title: Serializers
---

# Serializers

A <em>Serializer</em> structures your <em>Transformed</em> data in certain ways. There are many output
structures for APIs, two popular ones being [HAL] and [JSON-API]. Twitter and Facebook output data
differently to each other, and Google does it differently too. Most of the differences between these
serializers are how data is namespaced.

<em>Serializer</em> classes let you switch between various output formats with minimal effect on your <em>Transformers</em>.

[HAL]: http://stateless.co/hal_specification.html
[JSON-API]: http://jsonapi.org/

A very basic usage of Fractal will look like this, as has been seen in other sections:

~~~ php
use Acme\Model\Book;
use Acme\Transformer\BookTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\DataArraySerializer;

$manager = new Manager();
$manager->setSerializer(new DataArraySerializer());

// Some sort of ORM call
$book = Book::find(1);

// Make a resource out of the data and
$resource = new Item($book, new BookTransformer(), 'book');

// Run all transformers
$manager->createData($resource)->toArray();

// Outputs:
// [
//     'data' => [
//         'id' => 'Foo',
//         'title' => 'Foo',
//         'year' => 1991,
//     ],
// ];
~~~

What is new here is the `$manager->setSerializer(new DataArraySerializer());` part.
`DataArraySerializer` is the name of the default serializer in Fractal, but there are more.

## DataArraySerializer

This serializer is not to everyone's tastes, because it adds a `'data'` namespace to the output:

~~~ php
// Item
[
    'data' => [
        'foo' => 'bar'
    ],
];

// Collection
[
    'data' => [
        [
            'foo' => 'bar'
        ]
    ],
];
~~~

This is handy because it allows space for meta data (like pagination, or totals) in both Items and
Collections.

~~~ php
// Item with Meta
[
    'data' => [
        'foo' => 'bar'
    ],
    'meta' => [
        ...
    ]
];

// Collection with Meta
[
    'data' => [
        [
            'foo' => 'bar'
        ]
    ],
    'meta' => [
        ...
    ]
];
~~~

This fits in nicely for meta and included resources, using the `'data'` namespace. This means meta data
can be added for those included resources too.

~~~ php
// Item with included resource using meta
[
    'data' => [
        'foo' => 'bar'
        'comments' => [
            'data' => [
                ...
            ],
            'meta' => [
                ...
            ]
        ]
    ],
];
~~~


## ArraySerializer

Sometimes people want to remove that `'data'` namespace for items, and that can be done using the `ArraySerializer`. This is mostly the same, other than that namespace for items. Collections keep the
`'data'` namespace to avoid confusing JSON when meta data is added.

~~~ php
use League\Fractal\Serializer\ArraySerializer;
$manager->setSerializer(new ArraySerializer());
~~~

~~~ php
// Item
[
    'foo' => 'bar'
];

// Collection
[
    'data' => [
        'foo' => 'bar'
    ]
];
~~~

Meta data is fine for items, but gets a little confusing for collections:

~~~ php
// Item with Meta
[
    'foo' => 'bar'
    'meta' => [
        ...
    ]
];

// Collection with Meta
[
    [
        'foo' => 'bar'
    ]
    'meta' => [
        ...
    ]
];
~~~

Adding a named key to what is otherwise just a list confuses JSON:

> {"0":{"foo":"bar"},"meta":{}}

That `"0"` is there because you cannot mix index keys and non-indexed keys without JSON deciding to make
it a structure (object) instead of a list (array).

This is why ArraySerialzier is not recommended, but if you are not using meta data then... carry on.


## JsonApiSerializer

This is a representation of the [JSON-API] standard (v1.0). It implements the most common features such as

 - Primary Data
 - Resource Objects
 - Resource Identifier Objects
 - Compound Documents
 - Meta Information
 - Links
 - Relationships
 - Inclusion of Related Resources
 - Sparse Fieldsets

Features that are not yet included

 - Sorting
 - Pagination
 - Filtering

As Fractal is a library to output data structures, the serializer can only transform the content of your HTTP response. Therefore, the following has to be implemented by you

 - Content Negotiation
 - HTTP Response Codes
 - Error Objects

For more information please refer to the official [JSON API specification](http://jsonapi.org/format).

JSON API requires a _Resource Key_ for your resources, as well as an _id_ on every object.

~~~ php
use League\Fractal\Serializer\JsonApiSerializer;
$manager->setSerializer(new JsonApiSerializer());

// Important, notice the Resource Key in the third parameter:
$resource = new Item($book, new JsonApiBookTransformer(), 'books');
$resource = new Collection($books, new JsonApiBookTransformer(), 'books');
~~~

The resource key is used to give it a named namespace:

~~~ php
// Item
[
    'data' => [
        'type' => 'books',
        'id' => 1,
        'attributes' => [
            'foo' => 'bar'
        ],
    ],
];

// Collection
[
    'data' => [
        [
            'type' => 'books',
            'id' => 1,
            'attributes' => [
                'foo' => 'bar'
            ],
        ]
    ],
];
~~~

Just like `DataArraySerializer`, this works nicely for meta data:

~~~ php
// Item with Meta
[
    'data' => [
        'type' => 'books',
        'id' => 1,
        'attributes' => [
            'foo' => 'bar'
        ]
    ],
    'meta' => [
        ...
    ]
];

// Collection with Meta
[
    'data' => [
        [
            'type' => 'books',
            'id' => 1,
            'attributes' => [
                'foo' => 'bar'
            ]
        ]
    ],
    'meta' => [
        ...
    ]
];
~~~

Adding a resource to an item response would look like this:

~~~ php
// Item with a related resource
[
    'data' => [
        'type' => 'books',
        'id' => 1,
        'attributes' => [
            'foo' => 'bar'
        ],
        'relationships' => [
            'author' => [
                'data' => [
                    'type' => 'people',
                    'id' => '1',
                ]
            ]
        ]
    ],
    'included' => [
        [
            'type' => 'people',
            'id' => 1,
            'attributes' => [
                'name' => 'Dave'
            ]
        ]
    ]
];
~~~

If you want to enable `links` support, just set a _baseUrl_ on your serializer

~~~ php
use League\Fractal\Serializer\JsonApiSerializer;
$baseUrl = 'http://example.com';
$manager->setSerializer(new JsonApiSerializer($baseUrl));
~~~

The same resource as above will look like this

~~~ php
// Item with a related resource and links support
[
    'data' => [
        'type' => 'books',
        'id' => 1,
        'attributes' => [
            'foo' => 'bar'
        ],
        'links' => [
            'self' => 'http://example.com/books/1'
        ],
        'relationships' => [
            'author' => [
                'links' => [
                    'self' => 'http://example.com/books/1/relationships/author',
                    'related' => 'http://example.com/books/1/author'
                ],
                'data' => [
                    'type' => 'people',
                    'id' => '1',
                ]
            ]
        ]
    ],
    'included' => [
        [
            'type' => 'people',
            'id' => 1,
            'attributes' => [
                'name' => 'Dave'
            ],
            'links' => [
                'self' => 'http://example.com/people/1'
            ]
        ]
    ]
];
~~~

## Custom Serializers

You can make your own Serializers by implementing [SerializerAbstract].

~~~ php
use Acme\Serializer\CustomSerializer;
$manager->setSerializer(new CustomSerializer());
~~~

The structure of serializers will change at some point, to allow items and collections to be handled differently
and to improve side-loading logic. Keep an eye on the change log, but do not be afraid to make one.

[SerializerAbstract]: https://github.com/thephpleague/fractal/blob/master/src/Serializer/SerializerAbstract.php
