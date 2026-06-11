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
use Hyperf\Config\Listener\RegisterPropertyHandlerListener;
use Hyperf\Di\Aop\AstVisitorRegistry;
use Hyperf\Di\Aop\PropertyHandlerVisitor;
use Hyperf\Di\Aop\ProxyCallVisitor;
use Hyperf\Di\Aop\RegisterInjectPropertyHandler;

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', \SWOOLE_HOOK_ALL);

require_once BASE_PATH . '/vendor/autoload.php';

// Register AST visitors to the collector.
AstVisitorRegistry::insert(PropertyHandlerVisitor::class, \PHP_INT_MAX / 2);
AstVisitorRegistry::insert(ProxyCallVisitor::class, \PHP_INT_MAX / 2);

// Register Property Handler.
RegisterInjectPropertyHandler::register();

(new RegisterPropertyHandlerListener())->process(new stdClass());
