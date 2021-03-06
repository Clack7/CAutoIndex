<?php

use CAutoIndex\Config;
use CAutoIndex\Dir;
use CAutoIndex\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Index
 */
$app->get(Config::get('subDir') . '{path}', function(Request $request, $path) use ($app) {    
    $path  = $request->query->get('path', $path);

    try {
        $path = mb_convert_encoding($path, Config::get('fileSystemEncoding'), 'UTF-8');
        $dir = new Dir($path, true);
    } catch (\Exception $e) {
        $app->abort(404, $e->getMessage());
    }

    if ($request->isXmlHttpRequest()) {
        $list = $app['twig']->render('list.html.twig', array(
            'dir'   => $dir,
        ));

        $count = count($dir->getElements());
        return new JsonResponse(array(
            'list'    => $list,
            'parts'   => $dir->getParts(),
            'current' => array($dir->getUrl(false), $dir->getUrl(false, false)),
            'info'    => $count . ' ' . $app['translations'][$app['language']]['element' . ($count == 1 ? '' : 's')],
        ));
    }
    
    return $app['twig']->render('index.html.twig', array(
        'dir'   => $dir,
    ));
})
->assert('path', '^(?!_cai).*')
;

/**
 * Source code view
 */
$app->get(Config::get('subDir') . '_cai/{action}', function(Request $request, $action) use ($app) {
    switch ($action) {
        case 'code': //Source code view
            $path = $request->query->get('path', null);

            try {
                $path = mb_convert_encoding($path, Config::get('fileSystemEncoding'), 'UTF-8');
                $file   = new File($path, true);
                $source = $file->getSource();
            } catch (\Exception $e) {
                $app->abort(404, $e->getMessage());
            }
            
            $ext = $file->getExtension();
            $ext = $ext == 'htm' ? 'html' : ($ext == 'json' ? 'js' : $ext);
            if (!in_array($ext, array('php', 'html', 'css', 'js', 'sql', 'xml'))) {
                $ext = 'text';
            }
            
            return $app['twig']->render('code.html.twig', array(
                'file' => $file,
                'source' => $source,
                'ext'    => $ext,
            ));
            break;
        
        default:
            $app->abort(404, 'Invalid action.');
            break;
    }
})
->assert('action', '\w+')
;

/**
 * Error handler
 */
$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    $page = 404 == $code ? '404.html.twig' : '500.html.twig';

    return new Response($app['twig']->render($page), $code);
});
