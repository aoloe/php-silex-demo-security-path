# php-silex-demo-security-path

Securing the `admin/` path with Silex 2.0.

Implementing the basic snippets from <http://silex.sensiolabs.org/doc/master/providers/security.html> mixed with a few best practices from <https://github.com/lyrixx/Silex-Kitchen-Edition/>.



## Stackoverflow question

Having a hard time in getting `symfony/security/ to work together with Silex 2.0, I'm trying to create a complete implementation of the basic snippets from <http://silex.sensiolabs.org/doc/providers/security.html>.

It currently works without errors, but the `/admin` route alway shows the _login_ link and never the _logout_ one.  
There are three possible causes: the authentication does not work, it does not get stored in the session, or the template does not see it.

The full code is on Github (<https://github.com/aoloe/php-silex-demo-security-path>). Below you can find the most relevant files.

`web/index.php`:

    <?php

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    define('APP_BASEDIR', dirname(__DIR__));

    include_once(APP_BASEDIR.'/vendor/autoload.php');

    $app = new Aoloe\Demo\Application();

    $app->get('/admin', function(/* Request $request*/) use ($app) {

        return $app['twig']->render('admin.twig', [
            // 'content' => ($app['security.authorization_checker']->isGranted('ROLE_ADMIN') ? 'logged in' : 'not logged in'),
            'content' => 'Admin area',
        ]);
    });

    use Symfony\Component\HttpFoundation\Request;

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

`app/Application.php`:

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



`resources/template/login.twig`:

    <!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>title</title>
        </head>
        <body>
            <form action="{{ path('admin_login_check') }}" method="post">
                {{ error }}
                <p>admin/password</p>
                <input type="text" name="_username" value="{{ last_username }}" />
                <input type="password" name="_password" value="" />
                <input type="submit" value="Login" />
            </form>
        </body>
    </html>



`resources/template/admin.twig`:

    <!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>title</title>
        </head>
        <body>
            <h1>Admin</h1>
            <p>{{ content }}</p>
            <p>
            {% if is_granted('ROLE_ADMIN') %}
                <a href="{{ path('logout') }}">Logout</a>
            {% else %}
                <a href="{{ path('login') }}">Login</a>
            {% endif %}

            </p>
                
        </body>
    </html>

