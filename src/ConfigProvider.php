<?php

declare(strict_types=1);
/**
 * This file is part of 绿鸟科技.
 *
 * @link     https://www.greenbirds.cn
 * @document https://greenbirds.cn
 * @contact  liushaofan@greenbirds.cn
 */
namespace Gb\Framework;

use Gb\Framework\Exception\Handler\AuthExceptionHandler;
use Gb\Framework\Exception\Handler\CommonExceptionHandler;
use Gb\Framework\Exception\Handler\GuzzleRequestExceptionHandler;
use Gb\Framework\Exception\Handler\ValidationExceptionHandler;
use Gb\Framework\Middleware\CorsMiddleware;

class ConfigProvider
{
    public function __invoke(): array
    {
        $serviceMap = $this->serviceMap();

        return [
            'dependencies' => array_merge($serviceMap, [
            ]),
            'exceptions' => [
                'handler' => [
                    'http' => [
                        CommonExceptionHandler::class,
                        GuzzleRequestExceptionHandler::class,
                        ValidationExceptionHandler::class,
                        AuthExceptionHandler::class,
                    ],
                ],
            ],
            'middlewares' => [
                'http' => [
                    CorsMiddleware::class,
//                    ResponseMiddleware::class,
                ],
            ],
            'commands' => [
            ],
            'listeners' => [
                \Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler::class,
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
                    'id' => 'framework',
                    'description' => 'framework配置',
                    'source' => __DIR__ . '/../publish/framework.php',
                    'destination' => BASE_PATH . '/config/autoload/framework.php',
                ],
                [
                    'id' => 'dependencies',
                    'description' => '依赖配置',
                    'source' => __DIR__ . '/../publish/dependencies.php',
                    'destination' => BASE_PATH . '/config/autoload/dependencies.php',
                ],
            ],
        ];
    }

    /**
     * 模型服务与契约的依赖配置.
     * @param string $path 契约与服务的相对路径
     * @return array 依赖数据
     */
    protected function serviceMap(string $path = 'app'): array
    {
        $services = readFileName(BASE_PATH . '/' . $path . '/Service');
        $spacePrefix = ucfirst($path);

        $dependencies = [];
        foreach ($services as $service) {
            $dependencies[$spacePrefix . '\\Contract\\' . $service . 'Interface'] = $spacePrefix . '\\Service\\' . $service;
        }

        return $dependencies;
    }
}
