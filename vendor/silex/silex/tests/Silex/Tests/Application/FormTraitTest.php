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
use Silex\Provider\FormServiceProvider;

/**
 * FormTrait test cases.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @requires PHP 5.4
 */
class FormTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testForm()
    {
        $this->assertInstanceOf('Symfony\Component\Form\FormBuilder', $this->createApplication()->form());
    }

    public function createApplication()
    {
        $app = new FormApplication();
        $app->register(new FormServiceProvider());

        return $app;
    }
}
