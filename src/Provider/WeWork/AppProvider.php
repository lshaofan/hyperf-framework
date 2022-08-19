<?php

declare(strict_types=1);
/**
 * This file is part of 绿鸟科技.
 *
 * @link     https://www.greenbirds.cn
 * @document https://greenbirds.cn
 * @contact  liushaofan@greenbirds.cn
 */
namespace Gb\Framework\Provider\WeWork;

use Gb\Framework\Contract\WeWork\AppConfigurable;

class AppProvider extends AbstractProvider
{
    /**
     * @var AppConfigurable
     */
    protected mixed $service;

    /**
     * @return array app配置
     */
    protected function config(?string $wxCorpId = null, ?array $agentId = null): array
    {
        return $this->service->appConfig($wxCorpId, $agentId);
    }
}
