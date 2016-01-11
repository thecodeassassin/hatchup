<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace Hatchup\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\NotFound as notFoundBase;
use Slim\Views\Twig;

/**
 * Class notFound
 *
 * @package Hatchup\Handlers
 */
final class NotFound extends notFoundBase
{
    /**
     * @var Twig
     */
    private $view;

    /**
     * @param Twig $view
     */
    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        parent::__invoke($request, $response);

        $this->view->render($response, '404.twig');

        return $response->withStatus(404);
    }

}