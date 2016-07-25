# php-silex-demo-security-path

Securing the `admin/` path with Silex 2.0.

Implementing the basic snippets from <http://silex.sensiolabs.org/doc/master/providers/security.html> mixed with a few best practices from <https://github.com/lyrixx/Silex-Kitchen-Edition/>.



## Stackoverflow question

The full code is on Github (<https://github.com/aoloe/php-silex-demo-security-path>). Below you can find the most relevant parts.

`web/index.php`:

    <?php

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

`app/Application.php`:

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

            $app['monolog.options'] = [
                'monolog.logfile' => APP_BASEDIR.'/var/logs/app.log',
                'monolog.name' => 'app',
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
                'admin' => ['ROLE_ADMIN', 'password']
            ];

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
            <p>Admin Area.</p>
            <p>{{ content }}</p>
            <p>
            
            {% if is_granted('ROLE_ADMIN') %}
                <a href="{{ path('admin_logout') }}">Logout</a>
            {% else %}
                <a href="{{ path('login') }}">Login</a>
            {% endif %}

            </p>
                
        </body>
    </html>


## Remarks

### Firewall and routing paths

You have to make sure that the paths defined in the firewall rules are exactly the same
as the one in the routes (trailing / inclusive).

### Users and passwords

If you're defining the users in an array, you have to use unencoded passwords.

In production, you have to store a list of hashed passwords in an external resource
(and avoid calculating a new hash on each reload!).  
Use `$app['security.default_encoder']->encodePassword('password', '')` to encode passwords.
