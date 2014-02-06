<?php

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('Silex\Tests', __DIR__);

if (!class_exists('Symfony\Component\Form\Form')) {
    echo "You must install the dev dependencies using:\n";
    echo "    composer install --dev\n";
    exit(1);
}
