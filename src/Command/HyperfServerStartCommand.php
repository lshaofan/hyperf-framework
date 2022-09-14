<?php

declare(strict_types=1);
/**
 * This file is part of 绿鸟科技.
 *
 * @link     https://www.greenbirds.cn
 * @document https://greenbirds.cn
 * @contact  liushaofan@greenbirds.cn
 */
namespace Gb\Framework\Command;

use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Swoole\Coroutine\System;
use Swoole\Process;
use Symfony\Component\Console\Input\InputOption;

class HyperfServerStartCommand extends HyperfCommand
{
    public function __construct()
    {
        parent::__construct('gbServer:start');
    }

        public function configure()
        {
            parent::configure();
            $this->setDescription('server start -d');
            $this->addOption('daemonize', '-d', InputOption::VALUE_NONE, 'daemonize mode');
        }

        /**
         * @throws ContainerExceptionInterface
         * @throws NotFoundExceptionInterface
         */
        public function handle()
        {
            $php_path = getPhpPath();
            $option_daemonize = $this->input->hasOption('daemonize') ? $this->input->getOption('daemonize') : false;
            putenv('DAEMONIZE=' . json_encode($option_daemonize));

            $master_pid = getMasterPid();
            // // already running hyperf process
            if (! empty($master_pid) && Process::kill(intval($master_pid), 0)) {
                stdLog()->warning("server is already running by master pid {$master_pid}");
                return;
            }
            $rmCmd='cd '.BASE_PATH.' && rm -rf runtime/container';
            System::exec($rmCmd);
//            // only run daemonize mode
            if ($option_daemonize) {
                // $log_file = BASE_PATH . '/runtime/logs/';
                // if (!file_exists($log_file)) {
            //     mkdir($log_file);
                // }
                // $log_file .= 'hyperf.out.log';

            // support swoole-cli
                $cmd = $php_path . ' -d swoole.use_shortname=Off ' . getBinPath() . ' start > /dev/null 2>&1'; //  > /dev/null 2>&1
            // passthru($cmd);
                System::exec($cmd); // > /dev/null 2>&1 | >> $log_file 2>&1
                stdLog()->info('server start success');
            } else {
                // stdLog()->info('when this mode is started, there is no highlight color on the console');
                stdLog()->info("debug mode please use `{$php_path} ./bin/hyperf.php start` or `./start.sh`");
                stdLog()->info("daemonize mode please use `{$php_path} ./bin/hyperf.php server:start -d` or `./server.sh start -d`");
                // echo PHP_EOL;
                // passthru('php ' . BASE_PATH . '/bin/hyperf.php start');
                // System::exec('sh '.BASE_PATH.'/start.sh > '.$log_file);
            }
        }
}
