<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Locale test cases.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class LocaleTest extends \PHPUnit_Framework_TestCase
{
    public function testLocale()
    {
        $app = new Application();
        $app->get('/', function (Request $request) { return $request->getLocale(); });
        $response = $app->handle(Request::create('/'));
        $this->assertEquals('en', $response->getContent());

        $app = new Application();
        $app['locale'] = 'fr';
        $app->get('/', function (Request $request) { return $request->getLocale(); });
        $response = $app->handle(Request::create('/'));
        $this->assertEquals('fr', $response->getContent());

        $app = new Application();
        $app->get('/{_locale}', function (Request $request) { return $request->getLocale(); });
        $response = $app->handle(Request::create('/es'));
        $this->assertEquals('es', $response->getContent());
    }

    public function testLocaleInSubRequests()
    {
        $app = new Application();
        $app->get('/embed/{_locale}', function (Request $request) { return $request->getLocale(); });
        $app->get('/{_locale}', function (Request $request) use ($app) {
            return $request->getLocale().$app->handle(Request::create('/embed/es'), HttpKernelInterface::SUB_REQUEST)->getContent().$request->getLocale();
        });
        $response = $app->handle(Request::create('/fr'));
        $this->assertEquals('fresfr', $response->getContent());

        $app = new Application();
        $app->get('/embed', function (Request $request) { return $request->getLocale(); });
        $app->get('/{_locale}', function (Request $request) use ($app) {
            return $request->getLocale().$app->handle(Request::create('/embed'), HttpKernelInterface::SUB_REQUEST)->getContent().$request->getLocale();
        });
        $response = $app->handle(Request::create('/fr'));
        // locale in sub-request must be "en" as this is the value if the sub-request is converted to an ESI
        $this->assertEquals('frenfr', $response->getContent());
    }

    public function testLocaleWithBefore()
    {
        $app = new Application();
        $app->before(function (Request $request) use ($app) { $request->setLocale('fr'); });
        $app->get('/embed', function (Request $request) { return $request->getLocale(); });
        $app->get('/', function (Request $request) use ($app) {
            return $request->getLocale().$app->handle(Request::create('/embed'), HttpKernelInterface::SUB_REQUEST)->getContent().$request->getLocale();
        });
        $response = $app->handle(Request::create('/'));
        // locale in sub-request is "en" as the before filter is only executed for the main request
        $this->assertEquals('frenfr', $response->getContent());
    }
}
