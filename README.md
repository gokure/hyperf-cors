# CORS Middleware for Hyperf

[![Build Status](https://github.com/gokure/hyperf-cors/actions/workflows/tests.yml/badge.svg)](https://github.com/gokure/hyperf-cors/actions)
[![Latest Stable Version](https://img.shields.io/packagist/v/gokure/hyperf-cors.svg)](https://packagist.org/packages/gokure/hyperf-cors)
[![Total Downloads](https://img.shields.io/packagist/dt/gokure/hyperf-cors.svg)](https://packagist.org/packages/gokure/hyperf-cors)
[![GitHub license](https://img.shields.io/github/license/gokure/hyperf-cors)](LICENSE)

Implements [fruitcake/laravel-cors](https://github.com/fruitcake/laravel-cors) for Hyperf.

## Features

* Handles CORS pre-flight OPTIONS requests
* Adds CORS headers to your responses
* Match routes to only add CORS to certain Requests

## Installation

Require the `gokure/hyperf-cors` package in your `composer.json` and update your dependencies:

```sh
composer require gokure/hyperf-cors
```

## Global usage

To allow CORS for all your routes, add the `CorsMiddleware` middleware at the top of the property of `config/autoload/middlewares.php` file and set the `paths` property in the config (see Configuration below):

```php
'http' => [
    \Gokure\HyperfCors\CorsMiddleware::class,
    ...
],
```

## Configuration

The defaults are set in `config/autoload/cors.php`. Publish the config to copy the file to your own config:

```sh
php bin/hyperf.php vendor:publish gokure/hyperf-cors
```

> **Note:** When using custom headers, like `X-Auth-Token` or `X-Requested-With`, you must set the `allowed_headers` to include those headers. You can also set it to `['*']` to allow all custom headers.

> **Note:** If you are explicitly whitelisting headers, you must include `Origin` or requests will fail to be recognized as CORS.

### Options

| Option                   | Description                                                                                 | Default value |
| ------------------------ | ------------------------------------------------------------------------------------------- | ------------- |
| paths                    | You can enable CORS for 1 or multiple paths, eg. `['api/*'] `                               | `[]`          |
| allowed_origins          | Matches the request origin. Wildcards can be used, eg. `*.mydomain.com` or `mydomain.com:*` | `['*']`       |
| allowed_origins_patterns | Matches the request origin with `preg_match`.                                               | `[]`          |
| allowed_methods          | Matches the request method.                                                                 | `['*']`       |
| allowed_headers          | Sets the Access-Control-Allow-Headers response header.                                      | `['*']`       |
| exposed_headers          | Sets the Access-Control-Expose-Headers response header.                                     | `false`       |
| max_age                  | Sets the Access-Control-Max-Age response header.                                            | `0`           |
| supports_credentials     | Sets the Access-Control-Allow-Credentials header.                                           | `false`       |

`allowed_origins`, `allowed_headers` and `allowed_methods` can be set to `['*']` to accept any value.

> **Note:** For `allowed_origins` you must include the scheme when not using a wildcard, e.g. `['http://example.com', 'https://example.com']`. You must also take into account that the scheme will be present when using `allowed_origins_patterns`.

## License

Released under the MIT License, see [LICENSE](LICENSE).
