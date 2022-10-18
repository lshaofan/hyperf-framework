<?php

namespace Gb\Framework;
use Swoole\Coroutine\System;

class Upgrade
{
    public string $version;


    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function upgrade(): bool|array
    {
        $result = System::exec('sh '.BASE_PATH.'/server.sh restart');
        return $result;
    }
}