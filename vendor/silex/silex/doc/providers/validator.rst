ValidatorServiceProvider
========================

The *ValidatorServiceProvider* provides a service for validating data. It is
most useful when used with the *FormServiceProvider*, but can also be used
standalone.

Parameters
----------

none

Services
--------

* **validator**: An instance of `Validator
  <http://api.symfony.com/master/Symfony/Component/Validator/Validator.html>`_.

* **validator.mapping.class_metadata_factory**: Factory for metadata loaders,
  which can read validation constraint information from classes. Defaults to
  StaticMethodLoader--ClassMetadataFactory.

  This means you can define a static ``loadValidatorMetadata`` method on your
  data class, which takes a ClassMetadata argument. Then you can set
  constraints on this ClassMetadata instance.

* **validator.validator_factory**: Factory for ConstraintValidators. Defaults
  to a standard ``ConstraintValidatorFactory``. Mostly used internally by the
  Validator.

Registering
-----------

.. code-block:: php

    $app->register(new Silex\Provider\ValidatorServiceProvider());

.. note::

    The Symfony Validator Component comes with the "fat" Silex archive but not
    with the regular one. If you are using Composer, add it as a dependency to
    your ``composer.json`` file:

    .. code-block:: json

        "require": {
            "symfony/validator": "~2.3"
        }

Usage
-----

The Validator provider provides a ``validator`` service.

Validating Values
~~~~~~~~~~~~~~~~~

You can validate values directly using the ``validateValue`` validator
method::

    use Symfony\Component\Validator\Constraints as Assert;

    $app->get('/validate/{email}', function ($email) use ($app) {
        $errors = $app['validator']->validateValue($email, new Assert\Email());

        if (count($errors) > 0) {
            return (string) $errors;
        } else {
            return 'The email is valid';
        }
    });

Validating Associative Arrays
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Validating associative arrays is like validating simple values, with a
collection of constraints::

    use Symfony\Component\Validator\Constraints as Assert;

    class Book
    {
        public $title;
        public $author;
    }

    class Author
    {
        public $first_name;
        public $last_name;
    }

    $book = array(
        'title' => 'My Book',
        'author' => array(
            'first_name' => 'Fabien',
            'last_name'  => 'Potencier',
        ),
    );

    $constraint = new Assert\Collection(array(
        'title' => new Assert\Length(array('min' => 10)),
        'author' => new Assert\Collection(array(
            'first_name' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 10))),
            'last_name'  => new Assert\Length(array('min' => 10)),
        )),
    ));
    $errors = $app['validator']->validateValue($book, $constraint);

    if (count($errors) > 0) {
        foreach ($errors as $error) {
            echo $error->getPropertyPath().' '.$error->getMessage()."\n";
        }
    } else {
        echo 'The book is valid';
    }

Validating Objects
~~~~~~~~~~~~~~~~~~

If you want to add validations to a class, you can define the constraint for
the class properties and getters, and then call the ``validate`` method::

    use Symfony\Component\Validator\Constraints as Assert;

    $author = new Author();
    $author->first_name = 'Fabien';
    $author->last_name = 'Potencier';

    $book = new Book();
    $book->title = 'My Book';
    $book->author = $author;

    $metadata = $app['validator.mapping.class_metadata_factory']->getMetadataFor('Author');
    $metadata->addPropertyConstraint('first_name', new Assert\NotBlank());
    $metadata->addPropertyConstraint('first_name', new Assert\Length(array('min' => 10)));
    $metadata->addPropertyConstraint('last_name', new Assert\Length(array('min' => 10)));

    $metadata = $app['validator.mapping.class_metadata_factory']->getMetadataFor('Book');
    $metadata->addPropertyConstraint('title', new Assert\Length(array('min' => 10)));
    $metadata->addPropertyConstraint('author', new Assert\Valid());

    $errors = $app['validator']->validate($book);

    if (count($errors) > 0) {
        foreach ($errors as $error) {
            echo $error->getPropertyPath().' '.$error->getMessage()."\n";
        }
    } else {
        echo 'The author is valid';
    }

You can also declare the class constraint by adding a static
``loadValidatorMetadata`` method to your classes::

    use Symfony\Component\Validator\Mapping\ClassMetadata;
    use Symfony\Component\Validator\Constraints as Assert;

    class Book
    {
        public $title;
        public $author;

        static public function loadValidatorMetadata(ClassMetadata $metadata)
        {
            $metadata->addPropertyConstraint('title', new Assert\Length(array('min' => 10)));
            $metadata->addPropertyConstraint('author', new Assert\Valid());
        }
    }

    class Author
    {
        public $first_name;
        public $last_name;

        static public function loadValidatorMetadata(ClassMetadata $metadata)
        {
            $metadata->addPropertyConstraint('first_name', new Assert\NotBlank());
            $metadata->addPropertyConstraint('first_name', new Assert\Length(array('min' => 10)));
            $metadata->addPropertyConstraint('last_name', new Assert\Length(array('min' => 10)));
        }
    }

    $app->get('/validate/{email}', function ($email) use ($app) {
        $author = new Author();
        $author->first_name = 'Fabien';
        $author->last_name = 'Potencier';

        $book = new Book();
        $book->title = 'My Book';
        $book->author = $author;

        $errors = $app['validator']->validate($book);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                echo $error->getPropertyPath().' '.$error->getMessage()."\n";
            }
        } else {
            echo 'The author is valid';
        }
    });

.. note::

    Use ``addGetterConstraint()`` to add constraints on getter methods and
    ``addConstraint()`` to add constraints on the class itself.

Translation
~~~~~~~~~~~

To be able to translate the error messages, you can use the translator
provider and register the messages under the ``validators`` domain::

    $app['translator.domains'] = array(
        'validators' => array(
            'fr' => array(
                'This value should be a valid number.' => 'Cette valeur doit être un nombre.',
            ),
        ),
    );

For more information, consult the `Symfony2 Validation documentation
<http://symfony.com/doc/master/book/validation.html>`_.
