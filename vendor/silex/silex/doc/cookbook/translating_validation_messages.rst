Translating Validation Messages
===============================

When working with Symfony2 validator, a common task would be to show localized
validation messages.

In order to do that, you will need to register translator and point to
translated resources::

    $app->register(new Silex\Provider\TranslationServiceProvider(), array(
        'locale' => 'sr_Latn',
        'translator.domains' => array(),
    ));

    $app->before(function () use ($app) {
        $app['translator']->addLoader('xlf', new Symfony\Component\Translation\Loader\XliffFileLoader());
        $app['translator']->addResource('xlf', __DIR__.'/vendor/symfony/validator/Symfony/Component/Validator/Resources/translations/validators/validators.sr_Latn.xlf', 'sr_Latn', 'validators');
    });

And that's all you need to load translations from Symfony2 ``xlf`` files.
