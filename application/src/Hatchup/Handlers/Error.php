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
final class Error extends \Slim\Handlers\Error
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

        parent::__construct();
    }

    /**
     * @param ServerRequestInterface   $request
     * @param ResponseInterface        $response
     * @param \Exception $exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, \Exception $exception)
    {
        // Log the message
        $this->logger->critical($exception->getMessage());

        return $this->view->render($response, 'errors/500.twig')->withStatus(500);
    }
}
