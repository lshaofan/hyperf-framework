<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [

    'wework' => [
        'config' => [
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',

            'log' => [
                'level' => 'debug',
                'file' => BASE_PATH . '/runtime/logs/wechat.log',
            ],
        ],
        'default' => [
            'provider' => 'app',
        ],
        'providers' => [
            'app' => [
                //                'name'    => \Gb\Framework\Provider\WeWork\AppProvider::class,
                //                'service' => App\Model\Corp::class, //  需要实现 Gb\Framework\Contract\WeWork\AppConfigurable 接口
            ],
            'user' => [
                //                'name'    => \Gb\Framework\Provider\WeWork\UserProvider::class,
                //                'service' => App\Model\Corp::class, //  需要实现 Gb\Framework\Contract\WeWork\UserConfigurable 接口
            ],
            'externalContact' => [
                //                'name'    => \Gb\Framework\Provider\WeWork\ExternalContactProvider::class,
                //                'service' => App\Model\Corp::class, //  需要实现 Gb\Framework\Contract\WeWork\ExternalContactConfigurable 接口
            ],
            'agent' => [
                //                'name'    => \Gb\Framework\Provider\WeWork\AgentProvider::class,
                //                'service' => App\Model\Corp::class, //  需要实现 Gb\Framework\Contract\WeWork\AgentConfigurable 接口
            ],
        ],
    ],
];
