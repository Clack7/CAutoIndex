<?php

use Silex\Provider\MonologServiceProvider;

// include options
require __DIR__.'/options.php';

//Sub directory
if ($_SERVER['REQUEST_URI'] != '/')  {
    $a = $_SERVER['REQUEST_URI'];
    $b = $_SERVER['SCRIPT_NAME'];

    $len = strlen($a); $subDir = '';
    for ($i = 0; $i < $len; $i++) {
        if ($a[$i] != $b[$i]) {
            break;
        }
        $subDir .= $a[$i];
    }
    $app['subDir'] = empty($subDir) || $subDir == '/' ? '' : trim($subDir, '/') . '/';
} else {
    $app['subDir'] = '';
}

/*$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../logs/index_prod.log',
));*/