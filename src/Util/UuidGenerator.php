<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util;

use Rhumsaa\Uuid\Uuid;

class UuidGenerator
{
    public static function getUuid() : string
    {
        return (string) Uuid::uuid4();
    }
}
