<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use CAutoIndex\Dir;

$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.path'    => array(__DIR__.'/../templates'),
    'twig.options' => array('cache' => false, /*__DIR__.'/../cache/twig'*/),
));
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
}));

//Index\Dir
$app['index.dir'] = $app->share(function () use ($app) {
    $dir = new Dir($app['subDir']);
    if (isset($app['rootName']) && (string) $app['rootName'] != '') {
        $dir->setRootName($app['rootName']);
    }
    return $dir;
});

return $app;
