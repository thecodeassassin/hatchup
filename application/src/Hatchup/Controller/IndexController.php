<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace Hatchup\Controller;

use Hatchup\Controller;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class IndexController
 *
 * @package Hatchup\Controller
 */
class IndexController extends Controller
{

    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    /**
     * Index page
     *
     * @Route('/')
     *
     * @Method('GET')
     *
     * @return array
     * @throws \Exception
     */
    public function indexAction(Request $request, Response $response, $args)
    {

        $esClient = $this->getElasticSearchClient();

        var_dump($esClient->cluster()->health());

        return $this->render('index.html.twig', $args);
    }
}
