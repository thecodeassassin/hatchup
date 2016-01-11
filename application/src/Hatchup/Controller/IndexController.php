<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace Hatchup\Controller;

use Hatchup\Controller;

/**
 * Class IndexController
 *
 * @package Hatchup\Controller
 */
class IndexController extends Controller
{

    public function __construct()
    {
        parent::__construct();
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
    public function indexAction()
    {
        $esClient = $this->getElasticSearchClient();

//        var_dump($esClient->cluster()->health());

        return $this->render('index.html.twig');
    }
}