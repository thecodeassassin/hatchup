<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk <stephen@tca0.nl>
 */

namespace Hatchup;

use Elasticsearch\ClientBuilder;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
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
     * @var bool
     */
    protected $debug = false;

    /**
     * {@inheritdoc}
     */
    public function __construct($userSettings = [])
    {
        parent::__construct($userSettings);

        if ($userSettings['debug']) {
            $this->debug = $userSettings['debug'];
        }

        $this->config = Config::getInstance();

        $this->registerHandlers();
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
     * @return Logger
     */
    public static function getLogWriter($name)
    {

        $filename = LOG_DIR . '/'.$name.'.log';
        $config = Config::getInstance();

        switch ($config['log_level']) {
            case 'debug':
                $logLevel = Logger::DEBUG;
                break;
            case 'error':
                $logLevel = Logger::ERROR;
                break;
            case 'info':
                $logLevel = Logger::INFO;
                break;
            case 'warning':
                $logLevel = Logger::WARNING;
                break;
            case 'critical':
                $logLevel = Logger::CRITICAL;
                break;
            default:
                 $logLevel = Logger::ERROR;
        }

        $logger = new Logger('logger');

        $stream = new StreamHandler($filename, $logLevel);
        $fingersCrossed = new FingersCrossedHandler($stream, $logLevel);

        $logger->pushHandler($fingersCrossed);

        return $logger;
    }

    /**
     * @param string $name
     *
     * @return Logger
     */
    public static function openLog($name)
    {
        $logWriter = static::getLogWriter($name);
        $app = static::getInstance();

        if ($app) {
            $container = $app->getContainer();
            $container['Logger'] = function () use ($logWriter) {
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

        static::openLog('app.main');

        // Get container
        $container = $this->getContainer();
        $config = $this->config;

        // Register component on container
        $container['view'] = function ($container) {
            $view = new Twig(VIEWS_DIR, [
                'cache' => CACHE_DIR . '/views',
                'auto_reload' => $this->debug
            ]);
            $view->addExtension(new TwigExtension(
                $container['router'],
                $container['request']->getUri()
            ));

            return $view;
        };

        $container['elasticsearch'] = function ($container) use ($config) {
            if (!empty($config['elasticsearch_user']) && !empty($config['elasticsearch_pass'])) {
                $host = sprintf(
                    'http://%s:%s@%s:%d',
                    $config['elasticsearch_user'],
                    $config['elasticsearch_pass'],
                    $config['elasticsearch_host'],
                    $config['elasticsearch_port']
                );
            } else {
                $host = sprintf(
                    'http://%s:%s',
                    $config['elasticsearch_host'],
                    $config['elasticsearch_port']
                );
            }
            // limit the hosts to one since we access it from a ELB
            $client = ClientBuilder::create()
                ->setHosts([$host])
                ->build();

            if (!Util::checkHostAvailability($config['elasticsearch_host'], $config['elasticsearch_port'])) {
                throw new \Exception('The Elasticsearch cluster seems to be unavailable.');
            }

            return $client;
        };

        /**
         * @return Config
         */
        $container['config'] = function () use ($config) {
            return $config;
        };
    }

    /**
     * Register shared handlers
     */
    protected function registerHandlers()
    {
        $container = $this->getContainer();

        // register handlers
        $container['errorHandler'] = function ($container) {
            return new Handlers\Error($container);
        };

        $container['notFoundHandler'] = function ($container) {
            return new Handlers\NotFound($container);
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
