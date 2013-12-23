<?php

use CAutoIndex\Config;
use Silex\Provider\MonologServiceProvider;

// include options
require __DIR__.'/options.php';

//CAutoIndex Config
Config::set('explorablePath', realpath(__DIR__ . '/../../'));
Config::set('sysDirName', basename(realpath(__DIR__ . '/../')));
$subDir = trim(strtr(Config::get('explorablePath'), array(
    realpath($_SERVER['DOCUMENT_ROOT']) => '', 
    DIRECTORY_SEPARATOR => '/',
)), '/');
Config::set('subDir', empty($subDir) ? '' : $subDir . '/');
if (!$app['rootName']) { 
    if (Config::get('subDir')) {
        $parts = explode('/', trim(Config::get('subDir'), '/'));
        $rootName = end($parts);
    }

    $app['rootName'] = !empty($rootName) ? $rootName : $_SERVER['SERVER_NAME'];
}
Config::set('rootName', $app['rootName']);
Config::set('sysUrl', '/' . Config::get('subDir') . Config::get('sysDirName') . '/');
Config::set('ignoreElements', $app['ignoreElements']);
Config::set('fileSystemEncoding', $app['fileSystemEncoding']);


/*$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../logs/index_prod.log',
));*/