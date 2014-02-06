<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests\Provider;

use Silex\Application;
use Silex\Provider\FormServiceProvider;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormTypeGuesserChain;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testFormFactoryServiceIsFormFactory()
    {
        $app = new Application();
        $app->register(new FormServiceProvider());
        $this->assertInstanceOf('Symfony\Component\Form\FormFactory', $app['form.factory']);
    }

    public function testFormServiceProviderWillLoadTypeExtensions()
    {
        $app = new Application();

        $app->register(new FormServiceProvider());

        $app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function($extensions) {
            $extensions[] = new DummyFormTypeExtension();
            return $extensions;
        }));

        $form = $app['form.factory']->createBuilder('form', array())
            ->add('file', 'file', array('image_path' => 'webPath'))
            ->getForm();

        $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
    }

    public function testFormServiceProviderWillLoadTypeGuessers()
    {
        $app = new Application();

        $app->register(new FormServiceProvider());

        $app['form.type.guessers'] = $app->share($app->extend('form.type.guessers', function($guessers) {
            $guessers[] = new FormTypeGuesserChain(array());
            return $guessers;
        }));

        $this->assertInstanceOf('Symfony\Component\Form\FormFactory', $app['form.factory']);
    }
}

class DummyFormTypeExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return 'file';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('image_path'));
    }
}
