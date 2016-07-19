<?php
namespace Aoloe\Demo;

use \Silex\Application as SilexApplication;

class Application extends SilexApplication
{
    public function __construct()
    {
        parent::__construct();

        $app = $this;

        $app['debug'] = true;

        date_default_timezone_set('Europe/Zurich');

        $app['monolog.options'] = [
            'monolog.logfile' => APP_BASEDIR.'/var/logs/app.log',
            'monolog.name' => 'app',
            // 'monolog.level' => 300, // = Logger::WARNING
        ];

        $app->register(new \Silex\Provider\MonologServiceProvider(), $app['monolog.options']);

        $app->register(new \Silex\Provider\SecurityServiceProvider());
        $app->register(new \Silex\Provider\SessionServiceProvider());

        $app['security.firewalls'] = [
            'admin' => [
                'pattern' => '^/admin/',
                'form' => [
                    'login_path' => '/login',
                    'logout' => [
                        'logout_path' => '/admin/logout',
                        'invalidate_session' => true
                    ],
                    'default_target_path' => '/admin',
                    'check_path' => '/admin/login_check'
                ],
                'users' => [
                    'admin' => ['ROLE_ADMIN', $app['security.default_encoder']->encodePassword('password', '')],
                ],
            ],
        ];

        /*
        $app['security.utils'] = function ($app) {
            return new \Symfony\Component\Security\Http\Authentication\AuthenticationUtils($app['request_stack']);
        };
        */

        $app->boot();

        $app->register(new \Silex\Provider\TwigServiceProvider(), array(
            'twig.path' => APP_BASEDIR.'/resources/template',
        ));

    }
}
