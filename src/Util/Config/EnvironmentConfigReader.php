<?php


namespace LizardsAndPumpkins\Util\Config;

use LizardsAndPumpkins\Util\Config\Exception\EnvironmentConfigKeyIsEmptyException;
use LizardsAndPumpkins\Util\Config\Exception\EnvironmentConfigKeyIsNotAStringException;

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

    /**
     * @return EnvironmentConfigReader
     */
    public static function fromGlobalState()
    {
        return static::fromArray($_SERVER);
    }

    /**
     * @param string[] $environmentConfig
     * @return EnvironmentConfigReader
     */
    public static function fromArray(array $environmentConfig)
    {
        return new self($environmentConfig);
    }

    /**
     * @param string $configKey
     * @return bool
     */
    public function has($configKey)
    {
        $this->validateConfigKey($configKey);
        $normalizedKey = $this->normalizeConfigKey($configKey);
        return isset($this->environmentConfig[$normalizedKey]);
    }

    /**
     * @param string $configKey
     * @return null|string
     */
    public function get($configKey)
    {
        $this->validateConfigKey($configKey);
        $normalizedKey = $this->normalizeConfigKey($configKey);
        return isset($this->environmentConfig[$normalizedKey]) ?
            $this->environmentConfig[$normalizedKey] :
            null;
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private function getVariableType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }

    /**
     * @param string $configKey
     */
    private function validateConfigKey($configKey)
    {
        $this->validateConfigKeyIsString($configKey);
        $this->validateConfigKeyNotEmpty($configKey);
    }

    /**
     * @param string $configKey
     */
    private function validateConfigKeyIsString($configKey)
    {
        if (!is_string($configKey)) {
            $variableType = $this->getVariableType($configKey);
            $message = sprintf('The given environment configuration key is not a string: "%s"', $variableType);
            throw new EnvironmentConfigKeyIsNotAStringException($message);
        }
    }

    /**
     * @param string $configKey
     */
    private function validateConfigKeyNotEmpty($configKey)
    {
        if ('' === trim($configKey)) {
            $message = 'The given environment configuration key is empty.';
            throw new EnvironmentConfigKeyIsEmptyException($message);
        }
    }

    /**
     * @param string $configKey
     * @return string
     */
    private function normalizeConfigKey($configKey)
    {
        return self::ENV_VAR_PREFIX . strtoupper(str_replace(' ', '', $configKey));
    }
}
