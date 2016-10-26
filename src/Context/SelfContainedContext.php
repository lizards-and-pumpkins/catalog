<?php

declare(strict_types=1);

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
    public function __construct(array $contextParts)
    {
        $this->contextParts = $contextParts;
    }

    public function __toString() : string
    {
        return $this->combineParts($this->contextParts);
    }

    public function getIdForParts(string ...$requestedParts) : string
    {
        return $this->combineParts(array_intersect_key($this->contextParts, array_flip($requestedParts)));
    }

    public function getValue(string $code) : string
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
    public function getSupportedCodes() : array
    {
        return array_keys($this->contextParts);
    }

    public function supportsCode(string $code) : bool
    {
        return isset($this->contextParts[$code]);
    }

    public function isSubsetOf(Context $otherContext) : bool
    {
        foreach ($this->getSupportedCodes() as $code) {
            if (!$otherContext->supportsCode($code) || !$this->hasSameValue($otherContext, $code)) {
                return false;
            }
        }
        return true;
    }

    public function contains(Context $otherContext) : bool
    {
        return $otherContext->isSubsetOf($this);
    }

    /**
     * @param string[] $dataSet
     * @return bool
     */
    public function matchesDataSet(array $dataSet) : bool
    {
        foreach ($this->getSupportedCodes() as $code) {
            if (isset($dataSet[$code]) && $this->getValue($code) !== $dataSet[$code]) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return string[]
     */
    public function jsonSerialize() : array
    {
        return $this->contextParts;
    }

    /**
     * @param string[] $contextParts
     * @return string
     */
    private function combineParts(array $contextParts) : string
    {
        return implode('_', array_map(function ($key, $value) {
            return $key . ':' . $value;
        }, array_keys($contextParts), $contextParts));
    }

    private function hasSameValue(Context $otherContext, string $code) : bool
    {
        return $this->getValue($code) === $otherContext->getValue($code);
    }
}
