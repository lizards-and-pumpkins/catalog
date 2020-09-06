<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Config;

use LizardsAndPumpkins\Util\Config\Exception\EnvironmentConfigKeyIsEmptyException;
use LizardsAndPumpkins\Util\Config\Exception\EnvironmentConfigKeyIsNotSetException;

class EnvironmentConfigReader implements ConfigReader
{
    const ENV_VAR_PREFIX = 'LP_';
    
    /**
     * @var string[]
     */
    private $environmentConfig;

    /**
     * @param string[] $environmentConfig
     */
    private function __construct(array $environmentConfig)
    {
        $this->environmentConfig = $environmentConfig;
    }

    public static function fromGlobalState() : EnvironmentConfigReader
    {
        return static::fromArray($_SERVER);
    }

    /**
     * @param string[] $environmentConfig
     * @return EnvironmentConfigReader
     */
    public static function fromArray(array $environmentConfig) : EnvironmentConfigReader
    {
        return new self($environmentConfig);
    }

    public function has(string $configKey) : bool
    {
        $this->validateConfigKey($configKey);
        $normalizedKey = $this->normalizeConfigKey($configKey);
        return isset($this->environmentConfig[$normalizedKey]);
    }

    public function get(string $configKey) : string
    {
        $this->validateConfigKey($configKey);
        $normalizedKey = $this->normalizeConfigKey($configKey);

        if (! isset($this->environmentConfig[$normalizedKey])) {
            throw new EnvironmentConfigKeyIsNotSetException(sprintf('Environment variable "%s" not set', $configKey));
        }

        return $this->environmentConfig[$normalizedKey];
    }

    private function validateConfigKey(string $configKey): void
    {
        if ('' === trim($configKey)) {
            $message = 'The given environment configuration key is empty.';
            throw new EnvironmentConfigKeyIsEmptyException($message);
        }
    }

    private function normalizeConfigKey(string $configKey) : string
    {
        return self::ENV_VAR_PREFIX . strtoupper(str_replace(' ', '', $configKey));
    }
}
