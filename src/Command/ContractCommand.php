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
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;

#[Command]
class ContractCommand extends HyperfCommand
{
    use CommandTrait;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('gbGen:contract');
    }

    public function configure(): void
    {
        parent::configure();
        $this->setDescription('Gb - 生成Contract, 默认生成于 app/Contract 目录下');
        $this->configureTrait();
    }

    public function handle(): void
    {
        # # 获取配置
        [$models, $path] = $this->stubConfig();

        $namespace = $this->input->getOption('namespace');
        if (is_string($namespace)) {
            $this->createInterface($models, $path, $namespace);
        }
    }

    /**
     * 根据模型 创建服务契约.
     * @param array $models 模型名称
     * @param string $modelPath 模型路径
     * @phpstan-param array<string, string> $models
     */
    protected function createInterface(array $models, string $modelPath, string $namespace): void
    {
//        $interfaceSpace = ucfirst(str_replace(['/', 'Model'], ['\\', 'Contract'], $modelPath));
        # 将命名空间 中Model 替换为 Contract
        $interfaceSpace = str_replace('Model', 'Contract', $namespace);
        $interfacePath = str_replace('Model', 'Contract', $modelPath);

        $stub = file_get_contents(__DIR__ . '/stubs/Contract.stub');
        if (! $stub) {
            return;
        }

        foreach ($models as $model) {
            $interface = $model . 'Contract';
            $serviceFile = BASE_PATH . '/' . $interfacePath . '/' . $interface . '.php';
            $fileContent = str_replace(
                ['#INTERFACE#', '#INTERFACE_NAMESPACE#', '#MODEL#', '#MODEL_PLURA#'],
                [$interface, $interfaceSpace, $model, Str::plural($model)],
                $stub
            );
            $this->doTouch($serviceFile, $fileContent);
        }
    }
}
