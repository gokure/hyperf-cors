# CORS Middleware for Hyperf

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

~~Also, to allow CORS for exception responses, you need add the `CorsExceptionHandler` handler at the top of the property of `config/autoload/exceptions.php` file:~~

```php
'handler' => [
    'http' => [
        Gokure\HyperfCors\CorsExceptionHandler::class,
        ...
    ],
],
```

> **Note:** Since the version `1.1.0`, `CorsExceptionHandler` has been deprecated, and it will be removed since 2.0, you can move it out safely from `exceptions.php` file.

## Configuration

The defaults are set in `config/autoload/cors.php`. Publish the config to copy the file to your own config:

```sh
php bin/hyperf.php vendor:publish gokure/hyperf-cors
```

> **Note:** When using custom headers, like `X-Auth-Token` or `X-Requested-With`, you must set the `allowed_headers` to include those headers. You can also set it to `['*']` to allow all custom headers.

> **Note:** If you are explicitly whitelisting headers, you must include `Origin` or requests will fail to be recognized as CORS.

### Options

| Option                   | Description                                                              | Default value |
|--------------------------|--------------------------------------------------------------------------|---------------|
| paths                    | You can enable CORS for 1 or multiple paths, eg. `['api/*'] `            | `[]`          |
| allowed_origins          | Matches the request origin. Wildcards can be used, eg. `*.mydomain.com` or `mydomain.com:*`  | `['*']`       |
| allowed_origins_patterns | Matches the request origin with `preg_match`.                            | `[]`          |
| allowed_methods          | Matches the request method.                                              | `['*']`       |
| allowed_headers          | Sets the Access-Control-Allow-Headers response header.                   | `['*']`       |
| exposed_headers          | Sets the Access-Control-Expose-Headers response header.                  | `false`       |
| max_age                  | Sets the Access-Control-Max-Age response header.                         | `0`           |
| supports_credentials     | Sets the Access-Control-Allow-Credentials header.                        | `false`       |

`allowed_origins`, `allowed_headers` and `allowed_methods` can be set to `['*']` to accept any value.

> **Note:** For `allowed_origins` you must include the scheme when not using a wildcard, eg. `['http://example.com', 'https://example.com']`. You must also take into account that the scheme will be present when using `allowed_origins_patterns`.

## License

Released under the MIT License, see [LICENSE](LICENSE).
