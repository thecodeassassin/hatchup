<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace Hatchup\Handlers;

use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;

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

        return parent::__invoke($request, $response, $exception);
    }
}
