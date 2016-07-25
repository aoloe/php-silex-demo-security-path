<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('APP_BASEDIR', dirname(__DIR__));

include_once(APP_BASEDIR.'/vendor/autoload.php');

$app = new Aoloe\Demo\Application();

use Symfony\Component\HttpFoundation\Request;

$app->get('/admin', function(Request $request) use ($app) {

    return $app['twig']->render('admin.twig', [
        // 'content' => ($app['security.authorization_checker']->isGranted('ROLE_ADMIN') ? 'logged in' : 'not logged in'),
        'content' => 'Admin area',
    ]);
});

$app->get('/login', function(Request $request) use ($app) {
    return $app['twig']->render(
        'login.twig',
        [
            'error' => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username')
        ]
    );
})->bind('login');

$app->get('/admin/logout', function(Request $request) use ($app) {
    return $app->redirect($app['url_generator']->generate('home'));
});

$app->get('/', function(Request $request) use ($app) {
    return $app['twig']->render('index.twig', [
    ]);
})->bind('home');

$app->run();
