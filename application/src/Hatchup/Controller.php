<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk <stephen@tca0.nl>
 */

namespace Hatchup;

use Elasticsearch\Client;
use Hatchup\App\Config;
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
     * @var Config
     */
    protected $config;

    /**
     * @var Container
     */
    public $container;

    /**
     * Base controller constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->response = $this->container->response;
        $this->request = $this->container->request;
        $this->config = $this->container['config'];
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