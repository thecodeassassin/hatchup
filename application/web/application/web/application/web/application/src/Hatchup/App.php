<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk <stephen@tca0.nl>
 */

namespace Hatchup;

use Hatchup\Handlers\NotFound;
use Monolog\Handler\StreamHandler;
use \Slim\App as SlimApp;
use \Hatchup\App\Config as Config;
use \Hatchup\App\Exception as Exception;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

/**
 * Custom APP class
 *
 * @package Hatcup
 */
class App extends SlimApp
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var null
     */
    protected static $instance = null;

    /**
     * @var Twig
     */
    protected $view;

    /**
     * {@inheritdoc}
     */
    public function __construct($userSettings = [])
    {
        parent::__construct($userSettings);

        $this->config = Config::getInstance();

        $this->registerServices();
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed|null $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @param $name
     *
     * @return \Monolog\Logger
     */
    public static function getLogWriter($name)
    {

        $filename = LOG_DIR . '/'.$name.'.log';

        $logger = new \Monolog\Logger('logger');

        $stream = new \Monolog\Handler\StreamHandler($filename, \Monolog\Logger::DEBUG);
        $fingersCrossed = new \Monolog\Handler\FingersCrossedHandler($stream, \Monolog\Logger::ERROR);

        $logger->pushHandler($fingersCrossed);

        return $logger;
    }

    /**
     * @param string $name
     *
     * @return \Monolog\Logger
     */
    public static function openLog($name)
    {
        $logWriter = static::getLogWriter($name);
        $app = static::getInstance();

        if ($app) {
            $app['Logger'] = function () use ($logWriter) {
                return $logWriter;
            };
        }

        return $logWriter;
    }

    /**
     * Register the singletons used in the application
     */
    protected function registerServices()
    {
        // register the app as a singleton
        self::$instance = $this;

        static::openLog('error');

        // Get container
        $container = $this->getContainer();

        // Register component on container
        $container['view'] = function ($container) {
            $view = new Twig('\Hatchup\src\Views', [
                'cache' => CACHE_DIR . '/views'
            ]);
            $view->addExtension(new TwigExtension(
                $container['router'],
                $container['request']->getUri()
            ));

            return $view;
        };

        // register handlers
        $app['errorHandler'] = function ($container) {
            return new Handlers\Error($container['Logger']);
        };

        $app['notFoundHandler'] = function ($container) {
            return new NotFound($container['view'], function ($request, $response) use ($container) {
                return $container['response']
                    ->withStatus(404);
            });
        };
    }

    /**
     * @return App
     *
     * @throws Exception
     */
    public static function getInstance()
    {
        return static::$instance;
    }
}