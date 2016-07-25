<?php
namespace Aoloe\Demo;

use \Silex\Application as SilexApplication;

use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

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

        $app->register(
            new \Silex\Provider\SessionServiceProvider(),
            ['session.storage.save_path' => APP_BASEDIR.'/var/sessions']
        );
        $app->register(new \Silex\Provider\SecurityServiceProvider());

        $app['security.default_encoder'] = function ($app) {
            return new PlaintextPasswordEncoder();
        };

        $users = [
            // 'admin' => ['ROLE_ADMIN', $app['security.default_encoder']->encodePassword('password', 'abc')],
            // 'admin' => ['ROLE_ADMIN', $app['security.encoder.digest']->encodePassword('password', 'abc')],
            'admin' => ['ROLE_ADMIN', 'password'],
        ];

        $app['security.firewalls'] = [
            'admin' => [
                'pattern' => '^/admin',
                'form' => [
                    'login_path' => '/login',
                    'logout' => [
                        'logout_path' => '/admin/logout',
                        'target_url' => 'home',
                        'invalidate_session' => true
                    ],
                    'default_target_path' => '/admin',
                    'check_path' => '/admin/login_check'
                ],
                'users' => $users,
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
