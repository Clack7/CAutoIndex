<?php

use CAutoIndex\Config;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.path'    => array(__DIR__.'/../templates'),
    'twig.options' => array('cache' => false, /*__DIR__.'/../cache/twig'*/),
));
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addFunction('Config', new \Twig_SimpleFunction('Config', function($param) {
    	return Config::get($param);
    }));

    return $twig;
}));

return $app;
