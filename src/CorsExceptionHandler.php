<?php

declare(strict_types=1);

namespace Gokure\HyperfCors;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class CorsExceptionHandler
 * @package Gokure\HyperfCors
 * @deprecated remove from 2.0
 */
class CorsExceptionHandler extends ExceptionHandler
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        if (class_exists(CorsMiddleware::class) && $this->container->has(CorsMiddleware::class)) {
            $request = $this->container->get(RequestInterface::class);
            return $this->container->get(CorsMiddleware::class)->onRequestHandled($response, $request);
        }

        return $response;
    }

    public function isValid(Throwable $throwable): bool
    {
        return false;
    }
}
