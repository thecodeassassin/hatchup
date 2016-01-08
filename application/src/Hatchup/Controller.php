<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk <stephen@tca0.nl>
 */

namespace Hatchup;
use JsonSchema\Validator;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Log;
use Slim\App;


/**
 * Class BaseController
 *
 * @package Hatchup
 */
abstract class Controller
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var App
     */
    protected $app;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Json\Response $jsonResponse
     */
    protected $jsonResponse;

    /**
     * @var Information
     */
    protected $headerInformation;

    /**
     * Base controller constructor
     */
    public function __construct()
    {


        $this->app = self::getApp();
        $this->response = $this->app->response;
        $this->request = $this->app->request;
        $this->config = $this->app->getConfig();
        $this->jsonResponse = $this->app->jsonResponse;
    }

    /**
     * Outputs Json response
     *
     * @param array $data Array data to output as JSON
     */
    public function outputJson($data)
    {
        $this->response->body(json_encode($data));
    }

    /**
     * Get the application's kernel (Slim object)
     *
     * @param string $name
     *
     * @return App
     */
    protected function getApp($name = 'default')
    {
        return App::
    }

}