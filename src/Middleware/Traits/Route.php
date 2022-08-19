<?php

declare(strict_types=1);
/**
 * This file is part of Gb.
 * @link     https://mo.chat
 * @document https://Gb.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/Gb-cloud/Gb/blob/master/LICENSE
 */
namespace Gb\Framework\Middleware\Traits;

use FastRoute\Dispatcher;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Utils\ApplicationContext;
use Gb\Framework\Constants\ErrorCode;
use Gb\Framework\Exception\CommonException;

trait Route
{
    protected function formatRoute(): string
    {
        $request    = ApplicationContext::getContainer()->get(RequestInterface::class);
        $dispatched = $request->getAttribute(Dispatched::class);
        $dynRoute   = $request->getUri()->getPath();

        switch ($dispatched->status) {
            case Dispatcher::NOT_FOUND:
                throw new CommonException(ErrorCode::URI_NOT_FOUND);
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new CommonException(ErrorCode::INVALID_HTTP_METHOD);
            case Dispatcher::FOUND:
                $dynRoute = $dispatched->handler->route;
                if (!str_contains($dynRoute, '{')) {
                    break;
                }
                $dynRoute = preg_replace('/:.*?}($|\/)/', '}/', $dispatched->handler->route);
                $dynRoute = rtrim($dynRoute, '/');
        }

        return $dynRoute;
    }

    protected function whiteListAuth(array $whiteList = []): bool
    {
        if (empty($whiteList)) {
            return false;
        }

        $route = $this->formatRoute();
        if (in_array($route, $whiteList)) {
            return true;
        }

        return false;
    }
}
