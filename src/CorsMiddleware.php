<?php

declare(strict_types=1);
/**
 * This file is part of gokure/hyperf-cors.
 *
 * @link     https://github.com/gokure/hyperf-cors
 * @document https://github.com/gokure/hyperf-cors/blob/main/README.md
 * @contact  gokure@gmail.com
 * @license  https://github.com/hyperf/hyperf-cors/blob/main/LICENSE
 */

namespace Gokure\HyperfCors;

use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function __construct(protected Cors $cors, protected ContainerInterface $container)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Check if we're dealing with CORS and if we should handle it
        if (! $this->shouldRun($request)) {
            return $handler->handle($request);
        }

        // For Preflight, return the Preflight response
        if ($this->cors->isPreflightRequest($request)) {
            $response = $this->cors->handlePreflightRequest($request);

            return $this->cors->varyHeader($response, 'Access-Control-Request-Method');
        }

        $response = Context::get(ResponseInterface::class);

        if ($request->getMethod() === 'OPTIONS') {
            $response = $this->cors->varyHeader($response, 'Access-Control-Request-Method');
        }

        Context::set(ResponseInterface::class, $this->addHeaders($request, $response));

        // Handle the request
        return $handler->handle($request);
    }

    /**
     * Add the headers to the Response, if they don't exist yet.
     */
    public function addHeaders(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (! $response->hasHeader('Access-Control-Allow-Origin')) {
            $response = $this->cors->addActualRequestHeaders($response, $request);
        }

        return $response;
    }

    /**
     * Determine if the request has a URI that should pass through the CORS flow.
     */
    protected function shouldRun(ServerRequestInterface $request): bool
    {
        return $this->isMatchingPath($request);
    }

    /**
     * The the path from the config, to see if the CORS Service should run.
     */
    protected function isMatchingPath(ServerRequestInterface $request): bool
    {
        // Get the paths from the config or the middleware
        $uri = $request->getUri();
        $paths = $this->getPathsByHost($uri->getHost());

        foreach ($paths as $path) {
            if ($path !== '/') {
                $path = \trim($path, '/');
            }

            if (Str::is($path, (string) $uri) || Str::is($path, \trim($uri->getPath(), '/'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Paths by given host or string values in config by default.
     */
    protected function getPathsByHost(string $host): array
    {
        $paths = $this->container->get(ConfigInterface::class)->get('cors.paths', []);

        // If where are paths by given host
        return $paths[$host] ?? \array_filter($paths, static fn ($path) => \is_string($path));
    }
}
