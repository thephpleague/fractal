FormServiceProvider
===================

The *FormServiceProvider* provides a service for building forms in
your application with the Symfony2 Form component.

Parameters
----------

* **form.secret**: This secret value is used for generating and validating the
  CSRF token for a specific page. It is very important for you to set this
  value to a static randomly generated value, to prevent hijacking of your
  forms. Defaults to ``md5(__DIR__)``.

Services
--------

* **form.factory**: An instance of `FormFactory
  <http://api.symfony.com/master/Symfony/Component/Form/FormFactory.html>`_,
  that is used for build a form.

* **form.csrf_provider**: An instance of an implementation of the
  `CsrfProviderInterface
  <http://api.symfony.com/master/Symfony/Component/Form/Extension/Csrf/CsrfProvider/CsrfProviderInterface.html>`_,
  defaults to a `DefaultCsrfProvider
  <http://api.symfony.com/master/Symfony/Component/Form/Extension/Csrf/CsrfProvider/DefaultCsrfProvider.html>`_.

Registering
-----------

.. code-block:: php

    use Silex\Provider\FormServiceProvider;

    $app->register(new FormServiceProvider());

.. note::

    If you don't want to create your own form layout, it's fine: a default one
    will be used. But you will have to register the
    :doc:`translation provider <providers/translation>` as the default form
    layout requires it.

    If you want to use validation with forms, do not forget to register the
    :doc:`Validator provider <providers/validator>`.

.. note::

    The Symfony Form Component and all its dependencies (optional or not) comes
    with the "fat" Silex archive but not with the regular one.

    If you are using Composer, add it as a dependency to your
    ``composer.json`` file:

    .. code-block:: json

        "require": {
            "symfony/form": "~2.3"
        }

    If you are going to use the validation extension with forms, you must also
    add a dependency to the ``symfony/config`` and ```symfony/translation``
    components:

    .. code-block:: json

        "require": {
            "symfony/validator": "~2.3",
            "symfony/config": "~2.3",
            "symfony/translation": "~2.3"
        }

    The Symfony Form Component relies on the PHP intl extension. If you don't have
    it, you can install the Symfony Locale Component as a replacement:

    .. code-block:: json

        "require": {
            "symfony/locale": "~2.3"
        }

    If you want to use forms in your Twig templates, make sure to install the
    Symfony Twig Bridge:

    .. code-block:: json

        "require": {
            "symfony/twig-bridge": "~2.3"
        }

Usage
-----

The FormServiceProvider provides a ``form.factory`` service. Here is a usage
example::

    $app->match('/form', function (Request $request) use ($app) {
        // some default data for when the form is displayed the first time
        $data = array(
            'name' => 'Your name',
            'email' => 'Your email',
        );

        $form = $app['form.factory']->createBuilder('form', $data)
            ->add('name')
            ->add('email')
            ->add('gender', 'choice', array(
                'choices' => array(1 => 'male', 2 => 'female'),
                'expanded' => true,
            ))
            ->getForm();

        if ('POST' == $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();

                // do something with the data

                // redirect somewhere
                return $app->redirect('...');
            }
        }

        // display the form
        return $app['twig']->render('index.twig', array('form' => $form->createView()));
    });

And here is the ``index.twig`` form template (requires ``symfony/twig-
bridge``):

.. code-block:: jinja

    <form action="#" method="post">
        {{ form_widget(form) }}

        <input type="submit" name="submit" />
    </form>

If you are using the validator provider, you can also add validation to your
form by adding constraints on the fields::

    use Symfony\Component\Validator\Constraints as Assert;

    $app->register(new Silex\Provider\ValidatorServiceProvider());
    $app->register(new Silex\Provider\TranslationServiceProvider(), array(
        'translator.messages' => array(),
    ));

    $form = $app['form.factory']->createBuilder('form')
        ->add('name', 'text', array(
            'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
        ))
        ->add('email', 'text', array(
            'constraints' => new Assert\Email()
        ))
        ->add('gender', 'choice', array(
            'choices' => array(1 => 'male', 2 => 'female'),
            'expanded' => true,
            'constraints' => new Assert\Choice(array(1, 2)),
        ))
        ->getForm();

You can register form extensions by extending ``form.extensions``::

    $app['form.extensions'] = $app->share($app->extend('form.extensions', function ($extensions) use ($app) {
        $extensions[] = new YourTopFormExtension();

        return $extensions;
    }));


You can register form type extensions by extending ``form.type.extensions``::

    $app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function ($extensions) use ($app) {
        $extensions[] = new YourFormTypeExtension();

        return $extensions;
    }));

You can register form type guessers by extending ``form.type.guessers``::

    $app['form.type.guessers'] = $app->share($app->extend('form.type.guessers', function ($guessers) use ($app) {
        $guessers[] = new YourFormTypeGuesser();

        return $guessers;
    }));

Traits
------

``Silex\Application\FormTrait`` adds the following shortcuts:

* **form**: Creates a FormBuilder instance.

.. code-block:: php

    $app->form($data);

For more information, consult the `Symfony2 Forms documentation
<http://symfony.com/doc/2.3/book/forms.html>`_.
