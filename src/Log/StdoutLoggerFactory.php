<?php

declare(strict_types=1);
/**
 * This file is part of Gb.
 * @link     https://mo.chat
 * @document https://Gb.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/Gb-cloud/Gb/blob/master/LICENSE
 */
namespace Gb\Framework\Log;

use Psr\Container\ContainerInterface;

class StdoutLoggerFactory
{
    public function __invoke(ContainerInterface $container): \Psr\Log\LoggerInterface
    {
        return Log::get('sys');
    }
}
