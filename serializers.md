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
[Ember Data's RESTAdapter]: http://emberjs.com/api/data/classes/DS.RESTAdapter.html
[adapter for Ember.js]: http://emberjs.com/guides/models/the-rest-adapter/

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

Sometimes people want to remove that `'data'` namespace for tems, and that can be done using the `ArraySerializer`. This is mostly the same, other than that namespace for items. Collections keep the 
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

Meta data is is fine for items, but gets a little confusing for collections:

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

This is a work in progress representation of the [JSON-API] standard. It is included as it is partially working, but has some work left.

There are few differences with the `JsonApiSerializer`. The first is that it uses "side-loading" to include
other related resources, which is different from the "embedding" approach that is used to include resources
by the other two serializers.

The second is that it requires a _Resource Key_, which the other two do not.

~~~ php
use League\Fractal\Serializer\JsonApiSerializer;
$manager->setSerializer(new JsonApiSerializer());

// Important, notice the Resource Key in the third parameter:
$resource = new Item($book, new GenericBookTransformer(), 'book');
$resource = new Collection($books, new GenericBookTransformer(), 'books');
~~~

That resource key is used to give it a named namespace:

~~~ php
// Item
[
    'book' => [
        'foo' => 'bar'
    ],
];

// Collection
[
    'books' => [
        [
            'foo' => 'bar'
        ]
    ],
];
~~~

Just like `DataArraySerializer`, this works nicely for meta data:

~~~ php
// Item with Meta
[
    'book' => [
        'foo' => 'bar'
    ],
    'meta' => [
        ...
    ]
];

// Collection with Meta
[
    'books' => [
        [
            'foo' => 'bar'
        ]
    ],
    'meta' => [
        ...
    ]
];
~~~

Adding a resource to an item response would look like this:

~~~ php
// Item with included resource
[
    'book' => [
        'foo' => 'bar'
    ],
    'linked' => [
        'author' => [
            [
                'name' => 'Dave'
            ]
        ]
    ]
];
~~~


## EmberSerializer
This serializer presents data the way [Ember Data's RESTAdapter] consumes it. This is the default [adapter for Ember.js].

At first glance, the `EmberSerializer` might seem identical to the `JsonApiSerializer`. The difference is in the way it presents "side-loaded" resources.

Other than with `ArraySerializer` or `DataArraySerializer`, we need to set the _Resource Key_.

~~~ php
use League\Fractal\Serializer\EmberSerializer;
$manager->setSerializer(new EmberSerializer());

// As with JsonApiSerializer, set the Resource Key in the third parameter:
$resource = new Item($book, new GenericBookTransformer(), 'book');
$resource = new Collection($books, new GenericBookTransformer(), 'books');
~~~

This key is used for namespacing:

~~~ php
// Item
[
    'book' => [
        'foo' => 'bar'
    ],
];

// Collection
[
    'books' => [
        [
            'foo' => 'bar'
        ]
    ],
];
~~~

We can add [metadata](http://emberjs.com/guides/models/handling-metadata/):

~~~ php
// Item with Meta
[
    'book' => [
        'foo' => 'bar'
    ],
    'meta' => [
        ...
    ]
];

// Collection with Meta
[
    'books' => [
        [
            'foo' => 'bar'
        ]
    ],
    'meta' => [
        ...
    ]
];
~~~

And now for the difference with the `JsonApiSerializer`: The `EmberSerializer` doesn't wrap the results in a "linked" array. Instead, it pulls all included resources to the top level. Note that for Ember to understand this, you will need to place relationship keys in your `Transformers`.

~~~ php
// Item with included resource
[
    'book' => [
        'foo' => 'bar',
        'author' => 24
    ],
    'author' => [
        [
            'id' => 24,
            'name' => 'Dave'
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
