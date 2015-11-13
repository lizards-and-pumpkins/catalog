<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\EmptyVersionException;
use LizardsAndPumpkins\Exception\InvalidVersionException;

class DataVersion
{
    /**
     * @var string
     */
    private $version;

    /**
     * @param string $version
     * @return DataVersion
     */
    public static function fromVersionString($version)
    {
        if (!is_string($version) && !is_int($version) && !is_float($version)) {
            throw new InvalidVersionException('The specified version is invalid');
        }

        if (empty($version)) {
            throw new EmptyVersionException('The specified version is empty');
        }

        return new self((string) $version);
    }

    /**
     * @param string $version
     */
    private function __construct($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->version;
    }
}
