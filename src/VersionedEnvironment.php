<?php
namespace Brera\PoC;

class VersionedEnvironment implements Environment
{
    /**
     * @var DataVersion
     */
    private $version;

	/**
	 * @param DataVersion $version
	 */
    public function __construct(DataVersion $version)
    {
        $this->version = $version;
    }

	/**
	 * @return DataVersion
	 */
    public function getVersion()
    {
        return $this->version;
    }
}
