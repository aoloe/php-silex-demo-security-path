<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('APP_BASEDIR', dirname(__DIR__));

include_once(APP_BASEDIR.'/vendor/autoload.php');

use Symfony\Component\HttpFoundation\Request;

$app = new Aoloe\Demo\Application();

$app->get('/admin/', function(Request $request) use ($app) {

    return $app['twig']->render('admin.twig', [
        'content' => ($app['security.authorization_checker']->isGranted('ROLE_ADMIN') ? 'You\'re logged in.' : 'You\'re not logged in.'),
    ]);
})->bind('admin');

$app->get('/', function(Request $request) use ($app) {
    return $app['twig']->render('index.twig', [
    ]);
})->bind('home');

$app->get('/login', function(Request $request) use ($app) {
    return $app['twig']->render(
        'login.twig',
        [
            'error' => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username')
        ]
    );
})->bind('login');

$app->run();
