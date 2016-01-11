<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk <stephen@tca0.nl>
 */

namespace Hatchup;

use Elasticsearch\Client;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Hatchup\App;

/**
 * Class BaseController
 *
 * @package Hatchup
 */
abstract class Controller
{
    /**
     * @var App
     */
    public $app;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Container
     */
    public $container;

    /**
     * Base controller constructor
     */
    public function __construct()
    {
        $this->app = self::getApp();
        $this->container = $this->app->getContainer();

        $this->response = $this->container->response;
        $this->request = $this->container->request;
        $this->config = $this->app->getConfig();
    }

    /**
     * Outputs Json response
     *
     * @param array $data Array data to output as JSON
     */
    public function outputJson(array $data)
    {
        $body = $this->response->getBody();
        $body->write(json_encode($data));
    }

    /**
     * @return Client
     */
    public function getElasticSearchClient()
    {
        return $this->container['elasticsearch'];
    }

    /**
     * Get the application's kernel (Slim object)
     *
     * @return App
     */
    protected function getApp()
    {
        return \Hatchup\App::getInstance();
    }

    /**
     * Render a view
     *
     * @param string $view
     * @param array  $vars
     *
     * @return mixed
     */
    protected function render($view, array $vars = [])
    {
        return $this->container->view->render($this->container->response, $view, $vars);
    }
}