<?php


namespace LizardsAndPumpkins;

class EnvironmentConfigReader
{
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
        return isset($this->environmentConfig[$configKey]);
    }

    public function get($configKey)
    {
        return $this->has($configKey) ?
            $this->environmentConfig[$configKey] :
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
     * @param $configKey
     */
    private function validateConfigKey($configKey)
    {
        if (!is_string($configKey)) {
            $message = sprintf(
                'The given environment configuration key is not a string: "%s"',
                $this->getVariableType($configKey)
            );
            throw new Exception\EnvironmentConfigKeyIsNotAStringException($message);
        }
        
        if ('' === $configKey) {
            $message = 'The given environment configuration key is empty.';
            throw new Exception\EnvironmentConfigKeyIsEmptyException($message);
        }
    }
}
