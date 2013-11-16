<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->get($app['subDir'] . '{path}', function(Request $request, $path) use ($app) {
    $dir   = $app['index.dir'];
    $path  = $request->query->get('path', $path);
    $order = $request->query->get('ord', 'ext');
    $asc   = (bool) $request->query->get('asc', true);
    
    if (!$dir->explorePath($path, $order, $asc)) {
        $app->abort(404, 'File not found.');
    }
    
    if ($request->isXmlHttpRequest()) {
        $list = $app['twig']->render('list.html.twig', array(
            'dir'   => $dir,
            'order' => $order,
            'asc'   => $asc,
        ));

        $count = count($dir->getFiles());
        return new JsonResponse(array(
            'list'  => $list,
            'parts' => $dir->getParts(),
            'info'  => $count . ' elemento' . ($count == 1 ? '' : 's'),
        ));
    }
    
    return $app['twig']->render('index.html.twig', array(
        'dir'   => $dir,
        'order' => $order,
        'asc'   => $asc,
    ));
})
->assert('path', '^(?!_).*')
;

$app->get($app['subDir'] . '_code{path}', function(Request $request, $path) use ($app) {
    $path   = $request->query->get('path', $path);
    $dir    = $app['index.dir'];
    $source = $dir->getSource($path);
    
    if ($source === false) {
        $app->abort(404, 'File not found.');
    }
    
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $ext = $ext == 'htm' ? 'html' : ($ext == 'json' ? 'js' : $ext);
    if (!in_array($ext, array('php', 'html', 'css', 'js', 'sql', 'xml'))) {
        $ext = 'text';
    }
    
    return $app['twig']->render('code.html.twig', array(
        'dir'    => $dir,
        'path'   => $path,
        'source' => $source,
        'ext'    => $ext,
    ));
})
->assert('path', '.*')
;

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    $page = 404 == $code ? '404.html.twig' : '500.html.twig';

    return new Response($app['twig']->render($page, array(
        'code' => $code,
        'dir'  => $app['index.dir'],
    )), $code);
});
