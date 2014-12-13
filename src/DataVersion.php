<?php

namespace Brera\PoC;

class DataVersion
{
    /**
     * @param string $version
     *
     * @throws EmptyVersionException
     */
    public static function fromVersionString($version)
    {
        if (!is_string($version) && !is_int($version) && !is_float($version)) {
            throw new InvalidVersionException();
        }

        if (empty($version)) {
            throw new EmptyVersionException();
        }
    }
}
