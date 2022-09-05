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

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Utils\CodeGen\Project;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class ActionCommand extends HyperfCommand
{
    use CommandTrait;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('gbGen:action');
    }

    public function configure(): void
    {
        parent::configure();
        $this->setDescription('Gb - 生成action, 默认生成于 app/Action 目录下');
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_OPTIONAL,
            '是否强制覆盖',
            false
        );
        $this->addOption(
            'path',
            'ap',
            InputOption::VALUE_OPTIONAL,
            '控制器文件夹路径',
            'app/Action'
        );

        $this->addArgument('class', InputArgument::OPTIONAL, 'class名称', false);
    }

    public function handle(): void
    {
        # # 路径
        $dirPath = $this->input->getOption('path');
        # # 名称
        $name = $this->input->getArgument('class');

        if (! is_string($name) || ! is_string($dirPath)) {
            return;
        }
        $this->createActions($name, $dirPath);
    }

    /**
     * 创建资源控制器.
     * @param string $name name
     * @param string $dirPath 路径
     */
    protected function createActions(string $name, string $dirPath): void
    {
        $project = new Project();
        $class = $project->namespace($dirPath);

        $dirPath .= '/' . $name;
        # 将$name 中的 / 替换为 \
        $nameSpace = $class . str_replace('/', '\\', $name);
//        $nameSpace = ucfirst(str_replace('/', '\\', $dirPath));
        $lowerAction = lcfirst($name);

        $stub = file_get_contents(__DIR__ . '/stubs/Action.stub');

        $stubVars = [
            [$nameSpace, 'Index', $lowerAction . '/index', 'GET', '查询 - 列表'],
            [$nameSpace, 'Show', $lowerAction . '/show', 'GET', '查询 - 详情'],
            //            [$nameSpace, 'Create', $lowerAction . '/create', 'GET',  '添加 - 页面'],
            [$nameSpace, 'Store', $lowerAction . '/store', 'POST', '添加 - 动作'],
            //            [$nameSpace, 'Edit', $lowerAction . '/edit', 'GET',  '修改 - 页面'],
            [$nameSpace, 'Update', $lowerAction . '/update', 'PUT', '修改 - 动作'],
            [$nameSpace, 'Destroy', $lowerAction . '/destroy', 'DELETE', '删除 - 动作'],
        ];
        if (! is_string($stub)) {
            return;
        }

        foreach ($stubVars as $stubVar) {
            $serviceFile = BASE_PATH . '/' . $dirPath . '/' . $stubVar[1] . '.php';
            $fileContent = str_replace(
                ['#NAMESPACE#', '#ACTION#', '#ROUTE#', '#METHOD#', '#COMMENT#'],
                $stubVar,
                $stub
            );
            $this->doTouch($serviceFile, $fileContent);
        }
    }
}
