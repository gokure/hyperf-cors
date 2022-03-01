<?php

declare(strict_types=1);

namespace Gokure\HyperfCors\Tests\Stubs\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->stopPropagation();

        $status = $throwable instanceof ValidationException ? 422 : 500;
        return $response->withBody(new SwooleStream($throwable->getMessage()))->withStatus($status);
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
