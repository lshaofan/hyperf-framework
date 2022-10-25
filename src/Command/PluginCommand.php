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

use Gb\Framework\Event\PluginInstalled;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Coroutine\System;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class PluginCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    protected ?EventDispatcherInterface $eventDispatcher;

    /**
     * @var string 插件安装目录
     */
    protected string $installDir;

    /**
     * @var string 插件归档目录
     */
    protected string $archiveDir;

    /**
     * @var null|string|string[]
     */
    protected string|array|null $package;

    /**
     * @var null|string|string[]
     */
    protected string|array|null $version;

    /**
     * 插件的version信息.
     */
    protected array $pluginInfo;

    /**
     * @var string 包归档文件.zip
     */
    private string $archiveFile;

    /**
     * @var string 包安装目录
     */
    private string $pkgInstallDir;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->eventDispatcher = $container->get(EventDispatcherInterface::class);
        $pluginDir = config('framework.plugin.dir', BASE_PATH . '/addons');
        $this->installDir = $pluginDir;
        $this->archiveDir = $pluginDir . '/archive';

        parent::__construct('gb:plugin');
    }

    public function configure(): void
    {
        parent::configure();
        $this->addArgument('action', InputArgument::REQUIRED, '插件执行动作:安装[install], 卸载[remove]，更新[update]');
        $this->addArgument('package', InputArgument::REQUIRED, '包名称');
        $this->addArgument('version', InputArgument::OPTIONAL, '包版本', '*');
        $this->setDescription('插件安装/卸载/更新');
    }

    public function handle(): void
    {
        $action = $this->input->getArgument('action');
        $this->package = $this->input->getArgument('package');
        $this->version = $this->input->getArgument('version');
        $this->archiveFile = $this->archiveDir . '/' . $this->package . '.zip';
        $this->pkgInstallDir = $this->installDir . '/' . $this->package;

        dump($action, $this->package, $this->version, $this->archiveFile, $this->pkgInstallDir);
        switch ($action) {
            case 'install':
                $this->install();
//                $this->eventDispatcher->dispatch(new PluginInstalled([$this->package, $this->version]));
                break;
            case 'remove':
//                $this->uninstall();
         //                $this->eventDispatcher->dispatch(new PluginUninstalled([$this->package, $this->version]));
                break;
            case 'update':
         //                $this->update();
                break;
            default:
                $this->line('错误的执行动作,action:install/remove', 'warnning');
        }
    }

    /**
     * 插件安装.
     */
    protected function install(): void
    {
        $this->line(sprintf('插件[%s:%s]开始安装', $this->package, $this->version), 'info');
        # # 下载文件
        if (! $this->archiveDownload()) {
            return;
        }
        # # 解压
        if (! $this->zipExtract()) {
            return;
        }
        # # composer.install
        if (! $this->composerInstall()) {
            return;
        }
        # # 发布静态资源
        $this->staticPublish();
        # # mysql.plugin表数据添加
        $this->pluginDbInsert();
    }

    /*
     * 插件卸载
     */
    protected function uninstall(): void
    {
        $this->line(sprintf('插件[%s:%s]开始卸载', $this->package, $this->version), 'info');
        # # vendor链接删除
        if (! $this->composerUninstall()) {
            return;
        }
        # # plugin目录删除
        $this->delDir($this->pkgInstallDir);
        # # mysql.plugin表数据软删除
        $this->pluginDbDelete();
    }

    /**
     * src.发布静态资源.
     * @return bool 发布结果
     */
    protected function staticPublish(): bool
    {
        $publishSh = sprintf('php bin/hyperf.php vendor:publish %s -f', $this->pluginInfo['name']);

        $shRes = System::exec($publishSh);
        if ($shRes['signal'] === false || $shRes['code'] !== 0) {
            $falseMsg = '插件[静态资源]发布错误';
            isset($shRes['output']) && $falseMsg .= ':' . $shRes['output'];
            $this->line($shRes, 'error');
        }

        $this->line('[静态资源]发布完成', 'info');
        return true;
    }

    /**
     * 归档文件下载.
     * @param bool $isCover 是否覆盖下载
     * @return bool 下载结果
     */
    protected function archiveDownload(bool $isCover = false): bool
    {
        if ($isCover === false && file_exists($this->archiveFile)) {
            $this->line('gb插件[归档]已存在', 'info');
            return true;
        }

        # todo (插件市场)远程验证下载 - 待插件市功能
        $this->line('无本地归档插件，远程验证下载中...', 'info');
        $res = false;
        if ($res) {
            $this->line('gb插件[归档]下载完成', 'info');
        }

        $this->line('远程插件验证下载失败，请求检查验证key是否正确', 'error');
        return false;
    }

    /**
     * 解压归档文件.
     */
    protected function zipExtract(): bool
    {
        if (file_exists($this->pkgInstallDir . '/composer.json')) {    # 删除归档文件
//            $shRes = System::exec('rm -rf ' . $this->archiveFile);
            $this->line('gb插件[归档文件]已经解压', 'info');
            return true;
        }

        $zip = new \ZipArchive();
        if ($zip->open($this->archiveFile) !== true) {
            $this->line($this->archiveFile . 'gb插件[归档文件]打开失败', 'error');
            return false;
        }

        $extractRes = $zip->extractTo($this->pkgInstallDir);
        if (! $extractRes) {
            $this->line($this->archiveFile . 'gb插件[归档解压]失败', 'error');
            $zip->close();
            return false;
        }

        $zip->close();

        $this->line('gb插件[归档解压]完成', 'info');
        return true;
    }

    /**
     * 添加composer.repositories配置 + 链接vendor等.
     */
    protected function composerInstall(): bool
    {    # 获取取插件composer.json
        $composerJson = $this->pkgInstallDir . '/composer.json';
        if (! file_exists($composerJson)) {
            $this->line('gb插件[composer.json]不存在', 'error');
            return false;
        }
        $this->pluginInfo = json_decode(file_get_contents($composerJson), true);
        $repoSh = sprintf(
            'composer config repositories.%s path %s && composer require %s',
            $this->package,
            str_replace(BASE_PATH . '/', '', $this->pkgInstallDir),
            $this->pluginInfo['name']
        );
        $shRes = System::exec($repoSh);
        if ($shRes['signal'] === false || $shRes['code'] !== 0) {
            $falseMsg = '插件[composer链接]错误';
            isset($shRes['output']) && $falseMsg .= ':' . $shRes['output'];
            $this->line($shRes, 'error');
            return false;
        }
        $this->line('gb插件[composer链接]完成', 'info');
        return true;
    }

    /**
     * 添加composer.repositories配置 + 链接vendor等.
     */
    protected function composerUninstall(): bool
    {
        $repoSh = sprintf(
            'composer remove %s && composer config --unset repositories.%s',
            $this->package,
            $this->package
        );
        $shRes = System::exec($repoSh);
        if ($shRes['signal'] === false || $shRes['code'] !== 0) {
            $falseMsg = 'gb插件[composer.unlink]错误';
            isset($shRes['output']) && $falseMsg .= ':' . $shRes['output'];
            $this->line($shRes, 'error');
            return false;
        }

        $this->line('gb插件[composer.unlink]完成', 'info');
        return true;
    }

    /**
     * 数据库相关操作.
     */
    protected function pluginDbInsert(): bool
    {
        # 获取插件version.json
        $versionJson = $this->pkgInstallDir . '/version.json';
        $versionJson = json_decode(file_get_contents($versionJson), true);
        $versionJson['config'] = json_encode($versionJson['config']);
        Db::table('plugin')->updateOrInsert(['identifier' => $versionJson['identifier'],
        ], $versionJson);
        $this->line('插件安装完成，新的插件版本将会在下次重启服务后生效，立即重启请执行: php bin/hyperf.php server:start -d', 'info');
        return true;
    }

    /**
     * @deprecated  插件表数据删除(转到事件操作)
     */
    protected function pluginDbDelete(): bool
    {
//        $this->line('MoChat插件[表数据删除]完成', 'info');
        return true;
    }

    /**
     * 删除目录.
     * @param string $dirName ...
     * @return bool ...
     */
    protected function delDir(string $dirName): bool
    {
        if (! is_dir($dirName)) {
            return true;
        }
        $dir = opendir($dirName);
        while ($fileName = readdir($dir)) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }
            $file = $dirName . '/' . $fileName;
            if (is_dir($file)) {
                $this->delDir($file); // 使用递归删除目录
            } else {
                unlink($file);
            }
        }
        closedir($dir);

        if (rmdir($dirName)) {
            return true;
        }
        return false;
    }
}
