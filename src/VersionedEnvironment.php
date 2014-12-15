<?php
namespace Brera\PoC;

class VersionedEnvironment implements Environment
{
    /**
     * @var DataVersion
     */
    private $version;

    public function __construct(DataVersion $version)
    {
        $this->version = $version;
    }

    public function getVersion()
    {
        return $this->version;
    }
}
