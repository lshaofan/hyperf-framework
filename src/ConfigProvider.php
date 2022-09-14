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

use Gb\Framework\Command\HyperfReloadCommand;
use Gb\Framework\Command\HyperfReStartCommand;
use Gb\Framework\Command\HyperfServerStartCommand;
use Gb\Framework\Command\HyperfServerStatusCommand;
use Gb\Framework\Command\HyperfServerStopCommand;
use Gb\Framework\Exception\Handler\AuthExceptionHandler;
use Gb\Framework\Exception\Handler\CommonExceptionHandler;
use Gb\Framework\Exception\Handler\GuzzleRequestExceptionHandler;
use Gb\Framework\Exception\Handler\ValidationExceptionHandler;
use Gb\Framework\Middleware\CorsMiddleware;
use Swoole\Constant;

class ConfigProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        $serviceMap = $this->serviceMap();

        $config = [
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
                ],
            ],
            'commands' => [
                HyperfServerStartCommand::class,
                HyperfReloadCommand::class,
                HyperfReStartCommand::class,
                HyperfServerStatusCommand::class,
                HyperfServerStopCommand::class,
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
                ], [
                    'id' => 'server.sh',
                    'description' => 'The quick shell for server commands.',
                    'source' => __DIR__ . '/../publish/server.sh',
                    'destination' => BASE_PATH . '/server.sh',
                ],
                [
                    'id' => 'start.sh',
                    'description' => 'The quick shell for server commands.',
                    'source' => __DIR__ . '/../publish/start.sh',
                    'destination' => BASE_PATH . '/start.sh',
                ],
            ],
        ];
        $content = include BASE_PATH . '/config/autoload/server.php';
        $runtime_dir = dirname($content['settings'][Constant::OPTION_PID_FILE]);
        if (! file_exists($runtime_dir)) {
            mkdir($runtime_dir, 0777, true);
        }

        $option_daemonize = env('DAEMONIZE', false);
        if ($option_daemonize) {
            $log_dir = $runtime_dir . '/logs';
            if (! file_exists($log_dir)) {
                mkdir($log_dir, 0777, true);
            }
            $log_file = $log_dir . '/hyperf.out.log';
            $config['server']['settings'][Constant::OPTION_DAEMONIZE] = true;
            $config['server']['settings'][Constant::OPTION_LOG_FILE] = $log_file;
            // $config['server']['settings'][Constant::OPTION_RELOAD_ASYNC] = true; // 设置异步重启开关 swoole default
            // $config['server']['settings'][Constant::OPTION_MAX_WAIT_TIME] = 3; // 设置 Worker 进程收到停止服务通知后最大等待时间 swoole default
        }
        return $config;
    }

    /**
     * 模型服务与契约的依赖配置.
     * @param string $path 契约与服务的相对路径
     * @return array<mixed,mixed> 依赖数据
     */
    protected function serviceMap(string $path = 'app'): array
    {
        $services = readFileName(BASE_PATH . '/' . $path . '/Service');
        $spacePrefix = ucfirst($path);

        $dependencies = [];
        foreach ($services as $service) {
            $dependencies[$spacePrefix . '\\Contract\\' . $service . 'Contract'] = $spacePrefix . '\\Service\\' . $service;
        }

        return $dependencies;
    }
}
