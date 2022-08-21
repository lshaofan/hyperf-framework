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
class ServiceCommand extends HyperfCommand
{
    use CommandTrait;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('gbGen:service');
    }

    public function configure(): void
    {
        parent::configure();
        $this->setDescription('Gb - 生成service, 默认生成于 app/Service 目录下');
        $this->configureTrait();
    }

    public function handle(): void
    {
        # # 获取配置
        [$models, $path] = $this->stubConfig();
        # 获取命令参数 namespace
        $namespace = $this->input->getOption('namespace');
        if (is_string($namespace)) {
            $this->createServices($models, $path, $namespace);
        }
    }

    /**
     * 根据模型 创建服务
     * @param array $models 模型名称
     * @param string $modelPath 模型路径
     * @phpstan-param array<string, string> $models
     */
    protected function createServices(array $models, string $modelPath, string $namespace): void
    {
//        $modelSpace = ucfirst(str_replace('/', '\\', $modelPath));
        # 将命名空间 中Model 替换为 Service
        $serviceSpace = str_replace('Model', 'Service', $namespace);
//        $serviceSpace = str_replace('Model', 'Service', $modelSpace);
//        $interfaceSpace = str_replace('Model', 'Contract', $modelSpace);

        $stub = file_get_contents(__DIR__ . '/stubs/Service.stub');

        if (! $stub) {
            return;
        }
        foreach ($models as $model) {
            $modelSpace = $namespace;

            $interfaceSpace = str_replace('Model', 'Contract', $modelSpace);
            $serviceFile = BASE_PATH . '/' . str_replace('Model', 'Service', $modelPath) . '/' . $model . 'Service.php';
            $fileContent = str_replace(
                ['#MODEL#', '#MODEL_NAMESPACE#', '#SERVICE_NAMESPACE#', '#INTERFACE_NAMESPACE#', '#MODEL_PLURA#'],
                [$model, $modelSpace, $serviceSpace, $interfaceSpace, Str::plural($model)],
                $stub
            );
            $this->doTouch($serviceFile, $fileContent);
        }
    }
}
