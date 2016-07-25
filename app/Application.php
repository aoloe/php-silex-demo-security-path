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

        // You need to have unencoded passwords to define an array of users in the code itself.
        // In production, you have to store a list of hashed passwords in an external resource
        // (and avoid calculating a new hash on each reload!).
        // Use $app['security.default_encoder']->encodePassword('password', '') to encode passwords.
        $users = [
            'admin' => ['ROLE_ADMIN', 'password']
        ];

        // You have to make sure that the paths defined in the firewall rules are exactly the same
        // as the one in the routes (trailing / inclusive).
        $app['security.firewalls'] = [
            'admin' => [
                'pattern' => '^/admin/',
                'form' => [
                    'login_path' => '/login',
                    'default_target_path' => '/admin/',
                    'check_path' => '/admin/login_check'
                ],
                'logout' => [
                    'logout_path' => '/admin/logout',
                    'target_url' => 'admin',
                    'invalidate_session' => true
                ],
                'users' => $users,
            ],
        ];

        $app->boot();

        $app->register(new \Silex\Provider\TwigServiceProvider(), array(
            'twig.path' => APP_BASEDIR.'/resources/template',
        ));

    }
}
