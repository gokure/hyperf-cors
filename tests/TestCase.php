<?php

declare(strict_types=1);

namespace Gokure\HyperfCors\Tests;

use Gokure\HyperfCors\Cors;
use Gokure\HyperfCors\CorsMiddleware;
use Gokure\HyperfCors\Tests\Stubs\Exception\Handler\AppExceptionHandler;
use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Contract\TranslatorLoaderInterface;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Di\Container;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\CoreMiddleware;
use Hyperf\HttpServer\Request;
use Hyperf\HttpServer\ResponseEmitter;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Router;
use Hyperf\Translation\ArrayLoader;
use Hyperf\Translation\Translator;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Filesystem\Filesystem;
use Hyperf\Utils\Serializer\SimpleNormalizer;
use Hyperf\Utils\Waiter;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidatorFactory;
use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    protected function addWebRoutes()
    {
        Router::post('/web/ping', function () {
            return 'PONG';
        });
    }

    protected function addApiRoutes()
    {
        Router::post('/api/ping', function () {
            return 'PONG';
        });

        Router::put('/api/ping', function () {
            return 'PONG';
        });

        Router::post('/api/error', function () {
            throw new \RuntimeException('Server Error', 500);
        });

        Router::post('/api/validation', function () {
            ApplicationContext::getContainer()->get(ValidatorFactoryInterface::class)->validate([], [
                'name' => 'required',
            ]);

            return 'ok';
        });
    }

    protected function getContainer($config = [])
    {
        $corsConfig = array_merge([
            'paths' => ['api/*'],
            'supports_credentials' => false,
            'allowed_origins' => ['http://127.0.0.1'],
            'allowed_headers' => ['X-Custom-1', 'X-Custom-2'],
            'allowed_methods' => ['GET', 'POST'],
            'exposed_headers' => [],
            'max_age' => 0,
        ], $config);

        $container = Mockery::mock(Container::class);

        $container->shouldReceive('get')->with(HttpDispatcher::class)->andReturn(new HttpDispatcher($container));
        $container->shouldReceive('get')->with(ExceptionHandlerDispatcher::class)->andReturn(new ExceptionHandlerDispatcher($container));
        $container->shouldReceive('get')->with(ResponseEmitter::class)->andReturn(new ResponseEmitter());
        $container->shouldReceive('get')->with(DispatcherFactory::class)->andReturn($factory = new DispatcherFactory());
        $container->shouldReceive('get')->with(NormalizerInterface::class)->andReturn(new SimpleNormalizer());
        $container->shouldReceive('get')->with(MethodDefinitionCollectorInterface::class)->andReturn(new MethodDefinitionCollector());
        $container->shouldReceive('has')->with(ClosureDefinitionCollectorInterface::class)->andReturn(false);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'exceptions' => [
                'handler' => [
                    'http' => [
                        AppExceptionHandler::class,
                    ],
                ],
            ],
            'middlewares' => [
                'http' => [
                    CorsMiddleware::class,
                ],
            ],
            'cors' => $corsConfig,
        ]));
        $container->shouldReceive('get')->with(Filesystem::class)->andReturn(new Filesystem());
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('get')->with(RequestInterface::class)->andReturn(new Request());
        $container->shouldReceive('get')->with(ResponseInterface::class)->andReturn(new Response());
        $container->shouldReceive('get')->with(TranslatorLoaderInterface::class)->andReturn($loader = new ArrayLoader());
        $container->shouldReceive('get')->with(TranslatorInterface::class)->andReturn($translator = new Translator($loader, 'en'));
        $container->shouldReceive('get')->with(ValidatorFactoryInterface::class)->andReturn(new ValidatorFactory($translator, $container));
        $container->shouldReceive('get')->with(Cors::class)->andReturn($cors = new Cors($container));
        $container->shouldReceive('get')->with(CorsMiddleware::class)->andReturn(new CorsMiddleware($cors, $container));
        $container->shouldReceive('get')->with(AppExceptionHandler::class)->andReturn(new AppExceptionHandler());
        $container->shouldReceive('make')->with(CoreMiddleware::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new CoreMiddleware(...array_values($args));
        });
        $container->shouldReceive('get')->with(Waiter::class)->andReturn(new Waiter());
        ApplicationContext::setContainer($container);

        Router::init($factory);
        $this->addWebRoutes();
        $this->addApiRoutes();

        return $container;
    }
}
