<?php
namespace Brera\Environment;

use Brera\DataVersion;

class VersionedEnvironment implements Environment
{
    const CODE = 'version';
    
    /**
     * @var DataVersion
     */
    private $version;

    /**
     * @param array $environmentSource
     */
    public function __construct(array $environmentSource)
    {
        $this->version = $environmentSource[self::CODE];
    }

    /**
     * @param string $code
     * @return string
     *® @throws EnvironmentCodeNotFoundException
     */
    public function getValue($code)
    {
        if (self::CODE !== $code) {
            throw new EnvironmentCodeNotFoundException(sprintf(
                "No value was not found in the current environment for the code '%s'",
                $code
            ));
        }
        return (string) $this->version;
    }

    /**
     * @return string[]
     */
    public function getSupportedCodes()
    {
        return [self::CODE];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return 'v:' . $this->version;
    }
}
