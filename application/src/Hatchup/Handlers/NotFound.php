<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace Hatchup\Handlers;

use Hatchup\App;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Container;
use Slim\Views\Twig;

/**
 * Class Error
 *
 * @package Hatchup\Handlers
 */
final class NotFound extends \Slim\Handlers\NotFound
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Twig
     */
    protected $view;

    /**
     * @param Container $container
     *
     */
    public function __construct(Container $container)
    {
        $this->logger = App::openLog('app.error');
        $this->view = $container['view'];
    }

    /**
     * @param ServerRequestInterface   $request
     * @param ResponseInterface        $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        // Log the message
        $this->logger->addNotice(sprintf('Page %s was not found', (string)($request->getUri())));

        return $this->view->render($response, 'errors/404.twig')->withStatus(404);
    }
}
