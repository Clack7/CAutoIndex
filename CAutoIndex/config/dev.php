<?php

use Silex\Provider\MonologServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;

// include the prod configuration
require __DIR__.'/prod.php';

// enable the debug mode
$app['debug'] = true;

// Debug log
$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../logs/index_dev.log',
));