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
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\FormServiceProvider;
use Symfony\Component\Validator\Constraints as Assert;
use Silex\Tests\Provider\ValidatorServiceProviderTest\Constraint\Custom;
use Silex\Tests\Provider\ValidatorServiceProviderTest\Constraint\CustomValidator;

/**
 * ValidatorServiceProvider
 *
 * Javier Lopez <f12loalf@gmail.com>
 */
class ValidatorServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $app = new Application();

        $app->register(new ValidatorServiceProvider());

        return $app;
    }

    public function testRegisterWithCustomValidators()
    {
        $app = new Application();

        $app['custom.validator'] = $app->share(function() {
            return new CustomValidator();
        });

        $app->register(new ValidatorServiceProvider(), array(
            'validator.validator_service_ids' => array(
                'test.custom.validator' => 'custom.validator',
            )
        ));

        return $app;
    }

    /**
     * @depends testRegisterWithCustomValidators
     */
    public function testConstraintValidatorFactory($app)
    {
        $this->assertInstanceOf('Silex\ConstraintValidatorFactory', $app['validator.validator_factory']);

        $validator = $app['validator.validator_factory']->getInstance(new Custom());
        $this->assertInstanceOf('Silex\Tests\Provider\ValidatorServiceProviderTest\Constraint\CustomValidator', $validator);
    }

    /**
     * @depends testRegister
     */
    public function testValidatorServiceIsAValidator($app)
    {
        $this->assertInstanceOf('Symfony\Component\Validator\Validator', $app['validator']);
    }

    /**
     * @depends testRegister
     * @dataProvider testValidatorConstraintProvider
     */
    public function testValidatorConstraint($email, $isValid, $nbGlobalError, $nbEmailError, $app)
    {
        $app->register(new ValidatorServiceProvider());
        $app->register(new FormServiceProvider());

        $constraints = new Assert\Collection(array(
            'email' => array(
                new Assert\NotBlank(),
                new Assert\Email(),
            ),
        ));

        $builder = $app['form.factory']->createBuilder('form', array(), array(
            'constraints'     => $constraints,
            'csrf_protection' => false,
        ));

        $form = $builder
            ->add('email', 'email', array('label' => 'Email'))
            ->getForm()
        ;

        $form->bind(array('email' => $email));

        $this->assertEquals($isValid, $form->isValid());
        $this->assertEquals($nbGlobalError, count($form->getErrors()));
        $this->assertEquals($nbEmailError, count($form->offsetGet('email')->getErrors()));
    }

    public function testValidatorConstraintProvider()
    {
        // Email, form is valid , nb global error, nb email error
        return array(
            array('', false, 0, 1),
            array('not an email', false, 0, 1),
            array('email@sample.com', true, 0, 0),
        );
    }

}
