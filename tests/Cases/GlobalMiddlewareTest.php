<?php

declare(strict_types=1);

namespace Gokure\HyperfCors\Tests\Cases;

use Gokure\HyperfCors\Tests\TestCase;
use Hyperf\Testing\Client;

class GlobalMiddlewareTest extends TestCase
{
    public function testShouldReturnHeaderAssessControlAllowOriginWhenDontHaveHttpOriginOnRequest()
    {
        $container = $this->getContainer();
        $client = new Client($container);
        $crawler = $client->request('OPTIONS', '/api/ping', [
            'headers' => [
                'Access-Control-Request-Method' => 'POST',
            ],
        ]);

        $this->assertSame('http://127.0.0.1', $crawler->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertSame(204, $crawler->getStatusCode());
    }

    public function testOptionsAllowOriginAllowed()
    {
        $container = $this->getContainer();
        $client = new Client($container);
        $crawler = $client->request('OPTIONS', '/api/ping', [
            'headers' => [
                'Origin' => 'http://127.0.0.1',
                'Access-Control-Request-Method' => 'POST',
            ],
        ]);

        $this->assertSame('http://127.0.0.1', $crawler->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertSame(204, $crawler->getStatusCode());
    }

    public function testAllowAllOrigins()
    {
        $container = $this->getContainer(['allowed_origins' => ['*']]);
        $client = new Client($container);
        $crawler = $client->request('OPTIONS', '/api/ping', [
            'headers' => [
                'Origin' => 'http://hyperf.io',
                'Access-Control-Request-Method' => 'POST',
            ],
        ]);

        $this->assertSame('*', $crawler->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertSame(204, $crawler->getStatusCode());
    }

    public function testAllowAllOriginsWildcard()
    {
        $container = $this->getContainer(['allowed_origins' => ['*.hyperf.io']]);
        $client = new Client($container);
        $crawler = $client->request('OPTIONS', '/api/ping', [
            'headers' => [
                'Origin' => 'http://test.hyperf.io',
                'Access-Control-Request-Method' => 'POST',
            ],
        ]);

        $this->assertSame('http://test.hyperf.io', $crawler->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertSame(204, $crawler->getStatusCode());
    }

    public function testOriginsWildcardIncludesNestedSubdomains()
    {
        $container = $this->getContainer(['allowed_origins' => ['*.hyperf.io']]);
        $client = new Client($container);
        $crawler = $client->request('OPTIONS', '/api/ping', [
            'headers' => [
                'Origin' => 'http://api.service.test.hyperf.io',
                'Access-Control-Request-Method' => 'POST',
            ],
        ]);

        $this->assertSame('http://api.service.test.hyperf.io', $crawler->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertSame(204, $crawler->getStatusCode());
    }

    public function testAllowAllOriginsWildcardNoMatch()
    {
        $container = $this->getContainer(['allowed_origins' => ['*.hyperf.io']]);
        $client = new Client($container);
        $crawler = $client->request('OPTIONS', '/api/ping', [
            'headers' => [
                'Origin' => 'http://test.symfony.com',
                'Access-Control-Request-Method' => 'POST',
            ],
        ]);

        $this->assertEmpty($crawler->getHeader('Access-Control-Allow-Origin'));
    }

    public function testOptionsAllowOriginAllowedNonExistingRoute()
    {
        $container = $this->getContainer();
        $client = new Client($container);
        $crawler = $client->request('OPTIONS', '/api/pang', [
            'headers' => [
                'Origin' => 'http://127.0.0.1',
                'Access-Control-Request-Method' => 'POST',
            ],
        ]);

        $this->assertSame('http://127.0.0.1', $crawler->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertSame(204, $crawler->getStatusCode());
    }

    public function testOptionsAllowOriginNotAllowed()
    {
        $container = $this->getContainer();
        $client = new Client($container);
        $crawler = $client->request('OPTIONS', '/api/ping', [
            'headers' => [
                'Origin' => 'http://otherhost',
                'Access-Control-Request-Method' => 'POST',
            ],
        ]);

        $this->assertSame('http://127.0.0.1', $crawler->getHeaderLine('Access-Control-Allow-Origin'));
    }

    public function testAllowMethodAllowed()
    {
        $container = $this->getContainer();
        $client = new Client($container);
        $crawler = $client->request('POST', '/web/ping', [
            'headers' => [
                'Origin' => 'http://127.0.0.1',
                'Access-Control-Request-Method' => 'POST',
            ],
        ]);

        $this->assertEmpty($crawler->getHeader('Access-Control-Allow-Methods'));
        $this->assertSame(200, $crawler->getStatusCode());

        $this->assertSame('PONG', (string)$crawler->getBody());
    }

    public function testAllowMethodNotAllowed()
    {
        $container = $this->getContainer();
        $client = new Client($container);
        $crawler = $client->request('POST', '/web/ping', [
            'headers' => [
                'Origin' => 'http://127.0.0.1',
                'Access-Control-Request-Method' => 'PUT',
            ],
        ]);

        $this->assertEmpty($crawler->getHeader('Access-Control-Allow-Methods'));
        $this->assertSame(200, $crawler->getStatusCode());
    }

    public function testAllowHeaderAllowedOptions()
    {
        $container = $this->getContainer();
        $client = new Client($container);
        $crawler = $client->request('OPTIONS', '/api/ping', [
            'headers' => [
                'Origin' => 'http://127.0.0.1',
                'Access-Control-Request-Method' => 'POST',
                'Access-Control-Request-Headers' => 'x-custom-1, x-custom-2',
            ],
        ]);

        $this->assertSame('x-custom-1, x-custom-2', $crawler->getHeaderLine('Access-Control-Allow-Headers'));
        $this->assertSame(204, $crawler->getStatusCode());

        $this->assertSame('', (string)$crawler->getBody());
    }

    public function testAllowHeaderAllowedWildcardOptions()
    {
        $container = $this->getContainer(['allowed_headers' => ['*']]);
        $client = new Client($container);
        $crawler = $client->request('OPTIONS', '/api/ping', [
            'headers' => [
                'Origin' => 'http://127.0.0.1',
                'Access-Control-Request-Method' => 'POST',
                'Access-Control-Request-Headers' => 'x-custom-3',
            ],
        ]);

        $this->assertSame('x-custom-3', $crawler->getHeaderLine('Access-Control-Allow-Headers'));
        $this->assertSame(204, $crawler->getStatusCode());

        $this->assertSame('', (string)$crawler->getBody());
    }

    public function testAllowHeaderNotAllowedOptions()
    {
        $container = $this->getContainer();
        $client = new Client($container);
        $crawler = $client->request('OPTIONS', '/api/ping', [
            'headers' => [
                'Origin' => 'http://127.0.0.1',
                'Access-Control-Request-Method' => 'POST',
                'Access-Control-Request-Headers' => 'x-custom-3',
            ],
        ]);

        $this->assertSame('x-custom-1, x-custom-2', $crawler->getHeaderLine('Access-Control-Allow-Headers'));
    }

    public function testAllowHeaderAllowed()
    {
        $container = $this->getContainer();
        $client = new Client($container);
        $crawler = $client->request('POST', '/web/ping', [
            'headers' => [
                'Origin' => 'http://127.0.0.1',
                'Access-Control-Request-Headers' => 'x-custom-1, x-custom-2',
            ],
        ]);

        $this->assertEmpty($crawler->getHeader('Access-Control-Allow-Headers'));
        $this->assertSame(200, $crawler->getStatusCode());

        $this->assertSame('PONG', (string)$crawler->getBody());
    }

    public function testAllowHeaderAllowedWildcard()
    {
        $container = $this->getContainer(['allowed_headers' => ['*']]);
        $client = new Client($container);
        $crawler = $client->request('POST', '/web/ping', [
            'headers' => [
                'Origin' => 'http://127.0.0.1',
                'Access-Control-Request-Headers' => 'x-custom-3',
            ],
        ]);

        $this->assertEmpty($crawler->getHeader('Access-Control-Allow-Headers'));
        $this->assertSame(200, $crawler->getStatusCode());

        $this->assertSame('PONG', (string)$crawler->getBody());
    }

    public function testAllowHeaderNotAllowed()
    {
        $container = $this->getContainer();
        $client = new Client($container);
        $crawler = $client->request('POST', '/web/ping', [
            'headers' => [
                'Origin' => 'http://127.0.0.1',
                'Access-Control-Request-Headers' => 'x-custom-3',
            ],
        ]);

        $this->assertEmpty($crawler->getHeader('Access-Control-Allow-Headers'));
        $this->assertSame(200, $crawler->getStatusCode());
    }

    public function testError()
    {
        $container = $this->getContainer();
        $client = new Client($container);
        $crawler = $client->request('POST', '/api/error', [
           'headers' => [
               'Origin' => 'http://127.0.0.1',
               'Access-Control-Request-Method' => 'POST',
           ],
       ]);

        $this->assertSame('http://127.0.0.1', $crawler->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertSame(500, $crawler->getStatusCode());
    }

    public function testValidationException()
    {
        $container = $this->getContainer();
        $client = new Client($container);
        $crawler = $client->request('POST', '/api/validation', [
            'headers' => [
                'Origin' => 'http://127.0.0.1',
                'Access-Control-Request-Method' => 'POST',
            ],
            'form_params' => [],
        ]);

        $this->assertSame('http://127.0.0.1', $crawler->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertSame(422, $crawler->getStatusCode());
    }

    public function testInvalidExposedHeadersException()
    {
        $this->expectException(\RuntimeException::class);

        $container = $this->getContainer(['exposed_headers' => true]);
        $client = new Client($container);
        $client->request('POST', '/api/validation', [
            'headers' => [
                'Origin' => 'http://127.0.0.1',
            ],
        ]);
    }

    public function testInvalidOriginsException()
    {
        $this->expectException(\RuntimeException::class);

        $container = $this->getContainer(['allowed_origins' => true]);
        $client = new Client($container);
        $client->request('POST', '/api/validation', [
            'headers' => [
                'Origin' => 'http://127.0.0.1',
            ],
        ]);
    }
}
