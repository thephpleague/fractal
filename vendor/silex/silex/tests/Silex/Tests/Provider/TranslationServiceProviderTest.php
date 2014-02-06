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
use Silex\Provider\TranslationServiceProvider;

use Symfony\Component\HttpFoundation\Request;

/**
 * TranslationProvider test cases.
 *
 * @author Daniel Tschinder <daniel@tschinder.de>
 */
class TranslationServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Application
     */
    protected function getPreparedApp()
    {
        $app = new Application();

        $app->register(new TranslationServiceProvider());
        $app['translator.domains'] = array(
            'messages' => array(
                'en' => array (
                    'key1' => 'The translation',
                    'key_only_english' => 'Foo',
                    'key2' => 'One apple|%count% apples',
                    'test' => array(
                        'key' => 'It works'
                    )
                ),
                'de' => array (
                    'key1' => 'The german translation',
                    'key2' => 'One german apple|%count% german apples',
                    'test' => array(
                        'key' => 'It works in german'
                    )
                )
            )
        );
        return $app;
    }

    public function transChoiceProvider()
    {
        return array(
            array('key2', 0, null, '0 apples'),
            array('key2', 1, null, 'One apple'),
            array('key2', 2, null, '2 apples'),
            array('key2', 0, 'de', '0 german apples'),
            array('key2', 1, 'de', 'One german apple'),
            array('key2', 2, 'de', '2 german apples'),
            array('key2', 0, 'ru', '0 apples'), // fallback
            array('key2', 1, 'ru', 'One apple'), // fallback
            array('key2', 2, 'ru', '2 apples'), // fallback
        );
    }

    public function transProvider()
    {
        return array(
            array('key1', null, 'The translation'),
            array('key1', 'de', 'The german translation'),
            array('key1', 'ru', 'The translation'), // fallback
            array('test.key', null, 'It works'),
            array('test.key', 'de', 'It works in german'),
            array('test.key', 'ru', 'It works'), // fallback
        );
    }

    /**
     * @dataProvider transProvider
     */
    public function testTransForDefaultLanguage($key, $locale, $expected)
    {
        $app = $this->getPreparedApp();

        $result = $app['translator']->trans($key, array(), null, $locale);

        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider transChoiceProvider
     */
    public function testTransChoiceForDefaultLanguage($key, $number, $locale, $expected)
    {
        $app = $this->getPreparedApp();

        $result = $app['translator']->transChoice($key, $number, array('%count%' => $number), null, $locale);
        $this->assertEquals($expected, $result);
    }

    public function testBackwardCompatiblityForFallback()
    {
        $app = $this->getPreparedApp();
        $app['locale_fallback'] = 'de';

        $result = $app['translator']->trans('key1', array(), null, 'ru');
        $this->assertEquals('The german translation', $result);
    }

    public function testFallbacks()
    {
        $app = $this->getPreparedApp();
        $app['locale_fallbacks'] = array('de', 'en');

        // fallback to english
        $result = $app['translator']->trans('key_only_english', array(), null, 'ru');
        $this->assertEquals('Foo', $result);

        // fallback to german
        $result = $app['translator']->trans('key1', array(), null, 'ru');
        $this->assertEquals('The german translation', $result);
    }
}
