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
        if (!is_string($version)) {
            throw new InvalidVersionException(sprintf('Data version must be a string, got %s.', gettype($version)));
        }

        if (trim($version) === '') {
            throw new EmptyVersionException('The specified version is empty.');
        }

        return new self($version);
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
