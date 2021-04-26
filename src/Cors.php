<?php

declare(strict_types=1);

namespace Gokure\HyperfCors;

use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Cors
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $options;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $options = $this->container->get(ConfigInterface::class)->get('cors', []);
        $this->options = $this->normalizeOptions($options);
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function isCorsRequest(ServerRequestInterface $request): bool
    {
        return $request->hasHeader('Origin');
    }

    public function isPreflightRequest(ServerRequestInterface $request): bool
    {
        return $request->getMethod() === 'OPTIONS' && $request->hasHeader('Access-Control-Request-Method');
    }

    public function handlePreflightRequest(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->container->get(HttpResponse::class)->withStatus(204);

        return $this->addPreflightHeaders($response, $request);
    }

    public function addPreflightHeaders(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->configureAllowedOrigin($response, $request);

        if ($response->hasHeader('Access-Control-Allow-Origin')) {
            $response = $this->configureAllowCredentials($response, $request);

            $response = $this->configureAllowedMethods($response, $request);

            $response = $this->configureAllowedHeaders($response, $request);

            $response = $this->configureMaxAge($response, $request);
        }

        return $response;
    }

    public function isOriginAllowed(ServerRequestInterface $request): bool
    {
        if ($this->options['allowed_origins'] === true) {
            return true;
        }

        if (!$request->hasHeader('Origin')) {
            return false;
        }

        $origin = $request->getHeaderLine('Origin');

        if (in_array($origin, $this->options['allowed_origins'], true)) {
            return true;
        }

        if (Str::is($this->options['allowed_origins_patterns'], $origin)) {
            return true;
        }

        return false;
    }

    public function addActualRequestHeaders(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->configureAllowedOrigin($response, $request);

        if ($response->hasHeader('Access-Control-Allow-Origin')) {
            $response = $this->configureAllowCredentials($response, $request);

            $response = $this->configureExposedHeaders($response, $request);
        }

        return $response;
    }

    public function varyHeader(ResponseInterface $response, $header): ResponseInterface
    {
        if (!$response->hasHeader('Vary')) {
            return $response->withHeader('Vary', $header);
        }

        if (!in_array($header, explode(', ', $response->getHeaderLine('Vary')), true)) {
            return $response->withHeader('Vary', $response->getHeaderLine('Vary') . ', ' . $header);
        }

        return $response;
    }

    private function configureAllowedOrigin(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        if ($this->options['allowed_origins'] === true && !$this->options['supports_credentials']) {
            // Safe+cacheable, allow everything
            return $response->withHeader('Access-Control-Allow-Origin', '*');
        }

        if ($this->isSingleOriginAllowed()) {
            // Single origins can be safely set
            return $response->withHeader('Access-Control-Allow-Origin', array_values($this->options['allowed_origins'])[0]);
        }

        // For dynamic headers, set the requested Origin header when set and allowed
        if ($this->isCorsRequest($request) && $this->isOriginAllowed($request)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $request->getHeaderLine('Origin'));
        }

        return $this->varyHeader($response, 'Origin');
    }

    private function isSingleOriginAllowed(): bool
    {
        if ($this->options['allowed_origins'] === true || !empty($this->options['allowed_origins_patterns'])) {
            return false;
        }

        return count($this->options['allowed_origins']) === 1;
    }

    private function configureAllowedMethods(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        if ($this->options['allowed_methods'] === true) {
            $allowMethods = strtoupper($request->getHeaderLine('Access-Control-Request-Method'));
            $response = $this->varyHeader($response, 'Access-Control-Request-Method');
        } else {
            $allowMethods = implode(', ', $this->options['allowed_methods']);
        }

        return $response->withHeader('Access-Control-Allow-Methods', $allowMethods);
    }

    private function configureAllowedHeaders(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        if ($this->options['allowed_headers'] === true) {
            $allowHeaders = $request->getHeaderLine('Access-Control-Request-Headers');
            $response = $this->varyHeader($response, 'Access-Control-Request-Headers');
        } else {
            $allowHeaders = implode(', ', $this->options['allowed_headers']);
        }

        return $response->withHeader('Access-Control-Allow-Headers', $allowHeaders);
    }

    private function configureAllowCredentials(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        if ($this->options['supports_credentials']) {
            return $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }

    private function configureExposedHeaders(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        if ($this->options['exposed_headers']) {
            return $response->withHeader('Access-Control-Expose-Headers', implode(', ', $this->options['exposed_headers']));
        }

        return $response;
    }

    private function configureMaxAge(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        if ($this->options['max_age'] !== null) {
            return $response->withHeader('Access-Control-Max-Age', (int) $this->options['max_age']);
        }

        return $response;
    }

    private function normalizeOptions(array $options = []): array
    {
        $options += [
            'allowed_origins' => [],
            'allowed_origins_patterns' => [],
            'supports_credentials' => false,
            'allowed_headers' => [],
            'exposed_headers' => [],
            'allowed_methods' => [],
            'max_age' => 0,
        ];

        // normalize array('*') to true
        if (in_array('*', $options['allowed_origins'], true)) {
            $options['allowed_origins'] = true;
        }
        if (in_array('*', $options['allowed_headers'], true)) {
            $options['allowed_headers'] = true;
        } else {
            $options['allowed_headers'] = array_map('strtolower', $options['allowed_headers']);
        }

        if (in_array('*', $options['allowed_methods'], true)) {
            $options['allowed_methods'] = true;
        } else {
            $options['allowed_methods'] = array_map('strtoupper', $options['allowed_methods']);
        }

        return $options;
    }
}
