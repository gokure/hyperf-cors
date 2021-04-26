<?php

declare(strict_types=1);

namespace Gokure\HyperfCors;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'cors',
                    'description' => 'Adds CORS (Cross-Origin Resource Sharing) headers support in your Hyperf application.',
                    'source' => __DIR__ . '/../publish/cors.php',
                    'destination' => BASE_PATH . '/config/autoload/cors.php',
                ],
            ],
        ];
    }
}
