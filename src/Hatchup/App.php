<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk <stephen@tca0.nl>
 */

namespace Hatchup;

use Monolog\Handler\StreamHandler;
use \Slim\App as SlimApp;
use \Hatchup\App\Config as Config;
use \Hatchup\App\Exception as Exception;
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
     * @return Log
     */
    public static function openLog($name)
    {
        $logWriter = static::getLogWriter($name);
        $app = static::getInstance();

        $app['Logger'] = function() use ($logWriter) {
            return $logWriter;
        };
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
            $view = new \Slim\Views\Twig('\Hatchup\src\Views', [
                'cache' => CACHE_DIR . '/views'
            ]);
            $view->addExtension(new TwigExtension(
                $container['router'],
                $container['request']->getUri()
            ));

            return $view;
        };

        // register the errorHandler
        $app['errorHandler'] = function ($container) {
            return new Handlers\Error($container['Logger']);
        };
    }

    /**
     * @return App
     *
     * @throws Exception
     */
    public static function getInstance()
    {
        if (!is_object(static::$instance)) {
            throw new Exception('No instance of \Hatchup\App was initialized');
        }

        return static::$instance;
    }
}