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

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [],
            'commands' => [],
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
