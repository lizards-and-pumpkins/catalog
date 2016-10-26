<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\DataVersion;

use LizardsAndPumpkins\Context\DataVersion\Exception\EmptyVersionException;

class DataVersion
{
    const CONTEXT_CODE = 'version';
    
    /**
     * @var string
     */
    private $version;

    public static function fromVersionString(string $version) : DataVersion
    {
        if (trim($version) === '') {
            throw new EmptyVersionException('The specified version is empty.');
        }

        return new self($version);
    }

    private function __construct(string $version)
    {
        $this->version = $version;
    }

    public function __toString() : string
    {
        return $this->version;
    }
}
