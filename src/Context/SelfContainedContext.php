<?php


namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Context\Exception\ContextCodeNotFoundException;

class SelfContainedContext implements Context
{
    /**
     * @var string[]
     */
    private $contextParts;

    /**
     * @param string[] $contextParts
     */
    private function __construct(array $contextParts)
    {
        $this->contextParts = $contextParts;
    }

    /**
     * @param string[] $contextParts
     * @return SelfContainedContext
     */
    public static function fromArray(array $contextParts)
    {
        return new self($contextParts);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->combineParts($this->contextParts);
    }

    /**
     * @param string[] $requestedParts
     * @return string
     */
    public function getIdForParts(array $requestedParts)
    {
        return $this->combineParts(array_intersect_key($this->contextParts, array_flip($requestedParts)));
    }

    /**
     * @param string $code
     * @return string
     */
    public function getValue($code)
    {
        if (!isset($this->contextParts[$code])) {
            $message = sprintf('No value found in the current context for the code "%s"', $code);
            throw new ContextCodeNotFoundException($message);
        }
        return $this->contextParts[$code];
    }

    /**
     * @return string[]
     */
    public function getSupportedCodes()
    {
        return array_keys($this->contextParts);
    }

    /**
     * @param string $code
     * @return bool
     */
    public function supportsCode($code)
    {
        return isset($this->contextParts[$code]);
    }

    /**
     * @param Context $otherContext
     * @return bool
     */
    public function isSubsetOf(Context $otherContext)
    {
        foreach ($this->getSupportedCodes() as $code) {
            if (!$otherContext->supportsCode($code) || !$this->hasSameValue($otherContext, $code)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string[] $dataSet
     * @return bool
     */
    public function matchesDataSet(array $dataSet)
    {
        foreach ($this->getSupportedCodes() as $code) {
            if (!isset($dataSet[$code]) || $this->getValue($code) !== $dataSet[$code]) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return string[]
     */
    public function jsonSerialize()
    {
        return $this->contextParts;
    }

    /**
     * @param string[] $contextParts
     * @return string
     */
    private function combineParts(array $contextParts)
    {
        return implode('_', array_map(function ($key, $value) {
            return $key . ':' . $value;
        }, array_keys($contextParts), $contextParts));
    }

    /**
     * @param Context $otherContext
     * @param string $code
     * @return bool
     */
    private function hasSameValue(Context $otherContext, $code)
    {
        return $this->getValue($code) === $otherContext->getValue($code);
    }
}
