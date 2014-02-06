Phar File
=========

.. caution::

    Using the Silex ``phar`` file is deprecated. You should use Composer
    instead to install Silex and its dependencies or download one of the
    archives.

Installing
----------

Installing Silex is as easy as downloading the `phar
<http://silex.sensiolabs.org/get/silex.phar>`_ and storing it somewhere on
the disk. Then, require it in your script::

    <?php

    require_once __DIR__.'/silex.phar';

    $app = new Silex\Application();

    $app->get('/hello/{name}', function ($name) use ($app) {
        return 'Hello '.$app->escape($name);
    });

    $app->run();

Console
-------

Silex includes a lightweight console for updating to the latest version.

To find out which version of Silex you are using, invoke ``silex.phar`` on the
command-line with ``version`` as an argument:

.. code-block:: text

    $ php silex.phar version
    Silex version 0a243d3 2011-04-17 14:49:31 +0200

To check that your are using the latest version, run the ``check`` command:

.. code-block:: text

    $ php silex.phar check

To update ``silex.phar`` to the latest version, invoke the ``update``
command:

.. code-block:: text

    $ php silex.phar update

This will automatically download a new ``silex.phar`` from
``silex.sensiolabs.org`` and replace the existing one.

Pitfalls
--------

There are some things that can go wrong. Here we will try and outline the
most frequent ones.

PHP configuration
~~~~~~~~~~~~~~~~~

Certain PHP distributions have restrictive default Phar settings. Setting
the following may help.

.. code-block:: ini

    detect_unicode = Off
    phar.readonly = Off
    phar.require_hash = Off

If you are on Suhosin you will also have to set this:

.. code-block:: ini

    suhosin.executor.include.whitelist = phar

.. note::

    Ubuntu's PHP ships with Suhosin, so if you are using Ubuntu, you will need
    this change.

Phar-Stub bug
~~~~~~~~~~~~~

Some PHP installations have a bug that throws a ``PharException`` when trying
to include the Phar. It will also tell you that ``Silex\Application`` could not
be found. A workaround is using the following include line::

    require_once 'phar://'.__DIR__.'/silex.phar/autoload.php';

The exact cause of this issue could not be determined yet.

ioncube loader bug
~~~~~~~~~~~~~~~~~~

Ioncube loader is an extension that can decode PHP encoded file.
Unfortunately, old versions (prior to version 4.0.9) are not working well
with phar archives.
You must either upgrade Ioncube loader to version 4.0.9 or newer or disable it
by commenting or removing this line in your php.ini file:

.. code-block:: ini

    zend_extension = /usr/lib/php5/20090626+lfs/ioncube_loader_lin_5.3.so
