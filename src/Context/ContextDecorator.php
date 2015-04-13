<?php

namespace Brera\Context;

abstract class ContextDecorator implements Context
{
    /**
     * @var Context
     */
    private $component;

    /**
     * @var array
     */
    private $sourceData;

    /**
     * @param Context $component
     * @param mixed[] $sourceData
     */
    public function __construct(Context $component, array $sourceData)
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
            return $this->getValueFromContext();
        }
        return $this->component->getValue($code);
    }

    /**
     * @return string
     */
    protected function getValueFromContext()
    {
        return $this->defaultGetValueFromContextImplementation();
    }

    /**
     * @return string
     */
    private function defaultGetValueFromContextImplementation()
    {
        if (!array_key_exists($this->getCode(), $this->sourceData)) {
            throw new ContextCodeNotFoundException(sprintf(
                'No value found in the context source data for the code "%s"',
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
        return $this->buildIdString() . '_' . $this->component->getId();
    }

    /**
     * @return mixed[]
     */
    final protected function getSourceData()
    {
        return $this->sourceData;
    }


    /**
     * @param string $code
     * @return bool
     */
    public function supportsCode($code)
    {
        return $this->getCode() === $code || $this->component->supportsCode($code);
    }

    /**
     * @return string
     */
    private function buildIdString()
    {
        return $this->getCode() . ':' . $this->getValueFromContext();
    }

    /**
     * @param string[] $requestedParts
     * @return string
     */
    public function getIdForParts(array $requestedParts)
    {
        $componentPartialId = $this->component->getIdForParts($requestedParts);
        $myPartialId = $this->getMyIdIfRequested($requestedParts);
        $separator = $myPartialId && $componentPartialId ?
            '_' :
            '';
        return $myPartialId . $separator . $componentPartialId;
    }

    /**
     * @param string[] $requestedParts
     * @return string
     */
    private function getMyIdIfRequested(array $requestedParts)
    {
        return in_array($this->getCode(), $requestedParts) ?
            $this->buildIdString() :
            '';
    }
}
