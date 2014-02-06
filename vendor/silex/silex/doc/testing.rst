Testing
=======

Because Silex is built on top of Symfony2, it is very easy to write functional
tests for your application. Functional tests are automated software tests that
ensure that your code is working correctly. They go through the user
interface, using a fake browser, and mimic the actions a user would do.

Why
---

If you are not familiar with software tests, you may be wondering why you
would need this. Every time you make a change to your application, you have to
test it. This means going through all the pages and making sure they are still
working. Functional tests save you a lot of time, because they enable you to
test your application in usually under a second by running a single command.

For more information on functional testing, unit testing, and automated
software tests in general, check out `PHPUnit
<https://github.com/sebastianbergmann/phpunit>`_ and `Bulat Shakirzyanov's
talk on Clean Code
<http://www.slideshare.net/avalanche123/clean-code-5609451>`_.

PHPUnit
-------

`PHPUnit <https://github.com/sebastianbergmann/phpunit>`_ is the de-facto
standard testing framework for PHP. It was built for writing unit tests, but
it can be used for functional tests too. You write tests by creating a new
class, that extends the ``PHPUnit_Framework_TestCase``. Your test cases are
methods prefixed with ``test``::

    class ContactFormTest extends \PHPUnit_Framework_TestCase
    {
        public function testInitialPage()
        {
            ...
        }
    }

In your test cases, you do assertions on the state of what you are testing. In
this case we are testing a contact form, so we would want to assert that the
page loaded correctly and contains our form::

        public function testInitialPage()
        {
            $statusCode = ...
            $pageContent = ...

            $this->assertEquals(200, $statusCode);
            $this->assertContains('Contact us', $pageContent);
            $this->assertContains('<form', $pageContent);
        }

Here you see some of the available assertions. There is a full list available
in the `Writing Tests for PHPUnit
<http://www.phpunit.de/manual/current/en/writing-tests-for-phpunit.html>`_
section of the PHPUnit documentation.

WebTestCase
-----------

Symfony2 provides a WebTestCase class that can be used to write functional
tests. The Silex version of this class is ``Silex\WebTestCase``, and you can
use it by making your test extend it::

    use Silex\WebTestCase;

    class ContactFormTest extends WebTestCase
    {
        ...
    }

.. note::

    To make your application testable, you need to make sure you follow "Reusing
    applications" instructions from :doc:`usage`.

.. note::

    If you want to use the Symfony2 ``WebTestCase`` class you will need to
    explicitly install its dependencies for your project. Add the following to
    your ``composer.json`` file:

    .. code-block:: json

        "require-dev": {
            "symfony/browser-kit": ">=2.3,<2.4-dev"
        }

For your WebTestCase, you will have to implement a ``createApplication``
method, which returns your application. It will probably look like this::

        public function createApplication()
        {
            return require __DIR__.'/path/to/app.php';
        }

Make sure you do **not** use ``require_once`` here, as this method will be
executed before every test.

.. tip::

    By default, the application behaves in the same way as when using it from
    a browser. But when an error occurs, it is sometimes easier to get raw
    exceptions instead of HTML pages. It is rather simple if you tweak the
    application configuration in the ``createApplication()`` method like
    follows::

        public function createApplication()
        {
            $app = require __DIR__.'/path/to/app.php';
            $app['debug'] = true;
            $app['exception_handler']->disable();

            return $app;
        }

.. tip::

    If your application use sessions, set ``session.test`` to ``true`` to
    simulate sessions::

        public function createApplication()
        {
            // ...

            $app['session.test'] = true;

            // ...
        }

The WebTestCase provides a ``createClient`` method. A client acts as a
browser, and allows you to interact with your application. Here's how it
works::

        public function testInitialPage()
        {
            $client = $this->createClient();
            $crawler = $client->request('GET', '/');

            $this->assertTrue($client->getResponse()->isOk());
            $this->assertCount(1, $crawler->filter('h1:contains("Contact us")'));
            $this->assertCount(1, $crawler->filter('form'));
            ...
        }

There are several things going on here. You have both a ``Client`` and a
``Crawler``.

You can also access the application through ``$this->app``.

Client
------

The client represents a browser. It holds your browsing history, cookies and
more. The ``request`` method allows you to make a request to a page on your
application.

.. note::

    You can find some documentation for it in `the client section of the
    testing chapter of the Symfony2 documentation
    <http://symfony.com/doc/current/book/testing.html#the-test-client>`_.

Crawler
-------

The crawler allows you to inspect the content of a page. You can filter it
using CSS expressions and lots more.

.. note::

    You can find some documentation for it in `the crawler section of the testing
    chapter of the Symfony2 documentation
    <http://symfony.com/doc/current/book/testing.html#the-test-client>`_.

Configuration
-------------

The suggested way to configure PHPUnit is to create a ``phpunit.xml.dist``
file, a ``tests`` folder and your tests in
``tests/YourApp/Tests/YourTest.php``. The ``phpunit.xml.dist`` file should
look like this:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <phpunit backupGlobals="false"
             backupStaticAttributes="false"
             colors="true"
             convertErrorsToExceptions="true"
             convertNoticesToExceptions="true"
             convertWarningsToExceptions="true"
             processIsolation="false"
             stopOnFailure="false"
             syntaxCheck="false"
    >
        <testsuites>
            <testsuite name="YourApp Test Suite">
                <directory>./tests/</directory>
            </testsuite>
        </testsuites>
    </phpunit>

You can also configure a bootstrap file for autoloading and whitelisting for
code coverage reports.

Your ``tests/YourApp/Tests/YourTest.php`` should look like this::

    namespace YourApp\Tests;

    use Silex\WebTestCase;

    class YourTest extends WebTestCase
    {
        public function createApplication()
        {
            return require __DIR__.'/../../../app.php';
        }

        public function testFooBar()
        {
            ...
        }
    }

Now, when running ``phpunit`` on the command line, your tests should run.
