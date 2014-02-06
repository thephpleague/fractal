SwiftmailerServiceProvider
==========================

The *SwiftmailerServiceProvider* provides a service for sending email through
the `Swift Mailer <http://swiftmailer.org>`_ library.

You can use the ``mailer`` service to send messages easily. By default, it
will attempt to send emails through SMTP.

Parameters
----------

* **swiftmailer.options**: An array of options for the default SMTP-based
  configuration.

  The following options can be set:

  * **host**: SMTP hostname, defaults to 'localhost'.
  * **port**: SMTP port, defaults to 25.
  * **username**: SMTP username, defaults to an empty string.
  * **password**: SMTP password, defaults to an empty string.
  * **encryption**: SMTP encryption, defaults to null.
  * **auth_mode**: SMTP authentication mode, defaults to null.

  Example usage::

    $app['swiftmailer.options'] = array(
        'host' => 'host',
        'port' => '25',
        'username' => 'username',
        'password' => 'password',
        'encryption' => null,
        'auth_mode' => null
    );

Services
--------

* **mailer**: The mailer instance.

  Example usage::

    $message = \Swift_Message::newInstance();

    // ...

    $app['mailer']->send($message);

* **swiftmailer.transport**: The transport used for e-mail
  delivery. Defaults to a ``Swift_Transport_EsmtpTransport``.

* **swiftmailer.transport.buffer**: StreamBuffer used by
  the transport.

* **swiftmailer.transport.authhandler**: Authentication
  handler used by the transport. Will try the following
  by default: CRAM-MD5, login, plaintext.

* **swiftmailer.transport.eventdispatcher**: Internal event
  dispatcher used by Swiftmailer.

Registering
-----------

.. code-block:: php

    $app->register(new Silex\Provider\SwiftmailerServiceProvider());

.. note::

    SwiftMailer comes with the "fat" Silex archive but not with the regular
    one. If you are using Composer, add it as a dependency to your
    ``composer.json`` file:

    .. code-block:: json

        "require": {
            "swiftmailer/swiftmailer": ">=4.1.2,<4.2-dev"
        }

Usage
-----

The Swiftmailer provider provides a ``mailer`` service::

    $app->post('/feedback', function () use ($app) {
        $request = $app['request'];

        $message = \Swift_Message::newInstance()
            ->setSubject('[YourSite] Feedback')
            ->setFrom(array('noreply@yoursite.com'))
            ->setTo(array('feedback@yoursite.com'))
            ->setBody($request->get('message'));

        $app['mailer']->send($message);

        return new Response('Thank you for your feedback!', 201);
    });

Traits
------

``Silex\Application\SwiftmailerTrait`` adds the following shortcuts:

* **mail**: Sends an email.

.. code-block:: php

    $app->mail(\Swift_Message::newInstance()
        ->setSubject('[YourSite] Feedback')
        ->setFrom(array('noreply@yoursite.com'))
        ->setTo(array('feedback@yoursite.com'))
        ->setBody($request->get('message')));

For more information, check out the `Swift Mailer documentation
<http://swiftmailer.org>`_.
