<?php


namespace Brera\Environment;

abstract class EnvironmentDecorator implements Environment
{
    /**
     * @var Environment
     */
    private $component;
    
    /**
     * @var array
     */
    private $sourceData;

    public function __construct(Environment $component, array $sourceData)
    {
        $this->component = $component;
        $this->sourceData = $sourceData;
    }

    /**
     * @param string $code
     * @return string
     */
    final public function getValue($code)
    {
        if ($this->getCode() === $code) {
            return $this->getValueFromEnvironment();
        }
        return $this->component->getValue($code);
    }

    /**
     * @return string
     */
    protected function getValueFromEnvironment()
    {
        return $this->defaultGetValueFromEnvironmentImplementation();
    }

    /**
     * @return string
     */
    private function defaultGetValueFromEnvironmentImplementation()
    {
        if (! array_key_exists($this->getCode(), $this->sourceData)) {
            throw new EnvironmentCodeNotFoundException(sprintf(
                'No value found in the environment source data for the code "%s"',
                $this->getCode()
            ));
        }
        return $this->sourceData[$this->getCode()];
    }

    /**
     * @return string
     */
    abstract protected function getCode();

    /**
     * @return string[]
     */
    final public function getSupportedCodes()
    {
        return array_merge([$this->getCode()], $this->component->getSupportedCodes());
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getCode() . ':' . $this->getValueFromEnvironment() . '_' . $this->component->getId();
    }

    /**
     * @return array
     */
    final protected function getSourceData()
    {
        return $this->sourceData;
    }
}
