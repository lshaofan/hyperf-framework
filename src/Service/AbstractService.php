<?php

declare(strict_types=1);
/**
 * This file is part of 绿鸟科技.
 *
 * @link     https://www.greenbirds.cn
 * @document https://greenbirds.cn
 * @contact  liushaofan@greenbirds.cn
 */
namespace Gb\Framework\Service;

abstract class AbstractService
{
    /**
     * @var \Hyperf\Database\Model\Model
     */
    protected mixed $model;

    public function __construct()
    {
        $modelClass = str_replace(['\Service', 'Service'], ['\Model', ''], get_class($this));
        $this->model = make($modelClass);
    }
}
