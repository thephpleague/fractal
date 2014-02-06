<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests\Application;

use Silex\Application;
use Silex\Provider\TranslationServiceProvider;

/**
 * TranslationTrait test cases.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @requires PHP 5.4
 */
class TranslationTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testTrans()
    {
        $app = $this->createApplication();
        $app['translator'] = $translator = $this->getMockBuilder('Symfony\Component\Translation\Translator')->disableOriginalConstructor()->getMock();
        $translator->expects($this->once())->method('trans');
        $app->trans('foo');
    }

    public function testTransChoice()
    {
        $app = $this->createApplication();
        $app['translator'] = $translator = $this->getMockBuilder('Symfony\Component\Translation\Translator')->disableOriginalConstructor()->getMock();
        $translator->expects($this->once())->method('transChoice');
        $app->transChoice('foo', 2);
    }

    public function createApplication()
    {
        $app = new TranslationApplication();
        $app->register(new TranslationServiceProvider());

        return $app;
    }
}
