<?php


namespace LizardsAndPumpkins\Utils;

use Rhumsaa\Uuid\Uuid;

class UuidGenerator
{
    /**
     * @return string
     */
    public static function getUuid()
    {
        return (string) Uuid::uuid4();
    }
}
