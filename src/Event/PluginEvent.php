<?php

declare(strict_types=1);
/**
 * This file is part of Gb.
 * @link     https://mo.chat
 * @document https://Gb.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/Gb-cloud/Gb/blob/master/LICENSE
 */
namespace Gb\Framework\Event;

class PluginEvent
{
    protected mixed $package;

    protected mixed $version;

    public function __construct(array $config)
    {
        [$this->package, $this->version] = $config;
    }

    /**
     * 获取插件包名称.
     * @return string ...
     */
    public function getPackage(): string
    {
        return $this->package;
    }

    /**
     * 获取插件包版本.
     * @return string ...
     */
    public function getVersion(): string
    {
        return $this->package;
    }
}
