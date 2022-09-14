<?php

declare(strict_types=1);
/**
 * This file is part of 绿鸟科技.
 *
 * @link     https://www.greenbirds.cn
 * @document https://greenbirds.cn
 * @contact  liushaofan@greenbirds.cn
 */
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Phar\LoggerInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Swoole\Constant;
use Swoole\Coroutine\System;

if (! function_exists('readFileName')) {
    /**
     * 取出某目录下所有php文件的文件名.
     * @param string $path 文件夹目录
     * @return array 文件名
     */
    function readFileName(string $path): array
    {
        $data = [];
        if (! is_dir($path)) {
            return $data;
        }

        $files = scandir($path);
        foreach ($files as $file) {
            if (in_array($file, ['.', '..', '.DS_Store'])) {
                continue;
            }
            $data[] = preg_replace('/(\w+)\.php/', '$1', $file);
        }
        return $data;
    }
}
if (! function_exists('container')) {
    /**
     * 获取容器实例.
     * @return \Psr\Container\ContainerInterface
     */
    function container(): Psr\Container\ContainerInterface
    {
        return ApplicationContext::getContainer();
    }
}
if (! function_exists('redis')) {
    /**
     * 获取Redis实例.
     * @return \Hyperf\Redis\Redis
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function redis(): Hyperf\Redis\Redis
    {
        return container()->get(\Hyperf\Redis\Redis::class);
    }
}
if (! function_exists('responseDataFormat')) {
    function responseDataFormat($code, string $message = '', array $data = []): array
    {
        return [
            'code' => $code,
            'msg' => $message,
            'data' => $data,
        ];
    }
}

if (! function_exists('isDiRequestInit')) {
    function isDiRequestInit(): bool
    {
        try {
            \Hyperf\Utils\ApplicationContext::getContainer()->get(\Hyperf\HttpServer\Contract\RequestInterface::class)->input('test');
            $res = true;
        } catch (\TypeError $e) {
            $res = false;
        }
        return $res;
    }
}
if (! function_exists('stdLog')) {
    /**
     * 向控制台输出日志.
     * @return mixed|StdoutLoggerInterface
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    function stdLog()
    {
        return container()->get(StdoutLoggerInterface::class);
    }
}

if (! function_exists('logger')) {
    /**
     * 向日志文件记录日志.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function logger(): LoggerInterface
    {
        return container()->get(LoggerFactory::class)->make();
    }
}

if (! function_exists('getPids')) {
    /**
     * 获取服务所有pids.
     */
    function getPids(): array
    {
        $pids = [];
        $master_pid = getMasterPid();
        $pids[] = $master_pid;
        if ($master_pid) {
            // 获取manager pid
            $result = \Swoole\Coroutine\System::exec("ps -ef|grep {$master_pid}|grep -v grep|awk '{print $2}'");
            $result = trim($result['output']);
            $result = strlen($result) > 0 ? explode(PHP_EOL, $result) : [];
            foreach ($result as $value) {
                if ($master_pid != $value) {
                    // 获取manager创建的worker、task等工作进程pid
                    $tmp = \Swoole\Coroutine\System::exec("ps -ef|grep {$value}|grep -v grep|awk '{print $2}'");
                    $tmp = trim($tmp['output']);
                    $tmp = explode(PHP_EOL, $tmp);
                    $pids = array_merge($pids, $tmp);
                }
            }
        }
        // 进程号去重
        return array_unique($pids);
    }
}

if (! function_exists('getMasterPid')) {
    /**
     * 获取服务master pid.
     */
    function getMasterPid(): string
    {
        $master_pid = '';
        // 获取master pid
        $pid_file = config('server.settings')[Constant::OPTION_PID_FILE];
        if (file_exists($pid_file)) {
            $master_pid = file_get_contents($pid_file);
        }
        return trim($master_pid);
    }
}

if (! function_exists('getPhpPath')) {
    /**
     * 获取当前php命令路径.
     */
    function getPhpPath(): string
    {
        $pid = posix_getpid();
        if (PHP_OS == 'Darwin') {
            // macOS
            $result = System::exec("ps -e|grep {$pid}|grep -v grep|awk '{print $4}'");
        } else {
            // CentOS/Ubuntu
            $result = System::exec("ls -l /proc/{$pid}|grep exe|awk '{print $(NF)}'");
        }
        return trim($result['output']);
    }
}

if (! function_exists('getBinPath')) {
    /**
     * 获取当前php 执行代码文件路径.
     */
    function getBinPath(): string
    {
        return $_SERVER['argv'][0];
    }
}
