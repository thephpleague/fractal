Disable CSRF Protection on a form using the FormExtension
=========================================================

The *FormExtension* provides a service for building form in your application
with the Symfony2 Form component. By default, the *FormExtension* uses the
CSRF Protection avoiding Cross-site request forgery, a method by which a
malicious user attempts to make your legitimate users unknowingly submit data
that they don't intend to submit.

You can find more details about CSRF Protection and CSRF token in the
`Symfony2 Book
<http://symfony.com/doc/current/book/forms.html#csrf-protection>`_.

In some cases (for example, when embedding a form in an html email) you might
want not to use this protection. The easiest way to avoid this is to
understand that it is possible to give specific options to your form builder
through the ``createBuilder()`` function.

Example
-------

.. code-block:: php

    $form = $app['form.factory']->createBuilder('form', null, array('csrf_protection' => false));

That's it, your form could be submitted from everywhere without CSRF Protection.

Going further
-------------

This specific example showed how to change the ``csrf_protection`` in the
``$options`` parameter of the ``createBuilder()`` function. More of them could
be passed through this parameter, it is as simple as using the Symfony2
``getDefaultOptions()`` method in your form classes. `See more here
<http://symfony.com/doc/current/book/forms.html#book-form-creating-form-classes>`_.
