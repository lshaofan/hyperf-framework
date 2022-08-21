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
use Hyperf\Contract\ContainerInterface;
use Hyperf\Database\Commands\ModelCommand as HyperfModelCommand;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Utils\CodeGen\Project;
use Hyperf\Utils\Str;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class ModelCommand extends HyperfModelCommand
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        HyperfCommand::__construct('gbGen:model');
        $this->container = $container;
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addOption(
            'force-others',
            'fo',
            InputOption::VALUE_OPTIONAL,
            '是否强制覆盖modelTrait、service、serviceContract',
            false
        );
    }

    /**
     * 忽略模型重写.
     */
    protected function isIgnoreTable(string $table, ModelOption $option): bool
    {
        if (in_array($table, $option->getIgnoreTables())) {
            return true;
        }

        $prefix = $option->getPrefix();
        if (! str_contains($table, $prefix)) {
            return true;
        }
        # # 前缀忽略
        $tablePrefix = $this->config->get('databases.default.');

        return $table === $this->config->get('databases.migrations', 'migrations');
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass(string $table, string $name, ModelOption $option): string
    {
        $stub = file_get_contents(__DIR__ . '/stubs/Model.stub');

        return $this->replaceNamespace($stub, $name)
            ->replaceInheritance($stub, $option->getInheritance())
            ->replaceConnection($stub, $option->getPool())
            ->replaceUses($stub, $option->getUses())
            ->replaceClass($stub, $name)
            ->replaceTable($stub, $table);
    }

    /**
     * 模型生成重写.
     */
    protected function createModel(string $table, ModelOption $option)
    {
        # # 生成模型
        parent::createModel($table, $option);
        $project = new Project();
        # 获取模型命名空间
        $namespace = $project->namespace($option->getPath());
        # 删除namespace最后一个\
        $namespace = substr($namespace, 0, -1);
        $table = Str::replaceFirst($option->getPrefix(), '', $table);
        $forceService = $this->input->getOption('force-others') !== false;

        # # 生成服务契约
        $this->createServiceInterface($table, $option->getPath(), $forceService, $namespace);
        # # 生成服务
        $this->createService($table, $option->getPath(), $forceService, $namespace);
    }

    /**
     * 生成服务契约.
     * @param string $table 表名
     * @param string $modelPath 模型路径
     * @param bool $isForce 是否强制生成
     */
    protected function createServiceInterface(string $table, string $modelPath, bool $isForce, string $namespace): void
    {
        $this->call('gbGen:contract', [
            'table' => trim($table),
            '--model-path' => $modelPath,
            '--force' => $isForce,
            '--namespace' => $namespace,
        ]);
    }

    /**
     * 生成服务
     * @param string $table 表名
     * @param string $modelPath 模型路径
     * @param bool $isForce 是否强制生成
     */
    protected function createService(string $table, string $modelPath, bool $isForce, string $namespace): void
    {
        $this->call('gbGen:service', [
            'table' => trim($table),
            '--model-path' => $modelPath,
            '--force' => $isForce,
            '--namespace' => $namespace,
        ]);
    }
}
