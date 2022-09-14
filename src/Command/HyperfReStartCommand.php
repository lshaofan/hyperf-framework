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
use Symfony\Component\Console\Input\InputOption;

class HyperfReStartCommand extends HyperfCommand
{
    public function __construct()
    {
        parent::__construct('gbServer:restart');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('server restart');
        $this->addOption('default', '-d', InputOption::VALUE_NONE, 'restart server');
    }

    public function handle()
    {
        $this->call('gb:stop');
        $this->call('gb:start', ['-d' => true]);
    }
}
