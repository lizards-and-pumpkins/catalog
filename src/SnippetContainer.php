<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\InvalidSnippetContainerCodeException;

class SnippetContainer
{
    /**
     * @var string
     */
    private $containerCode;

    /**
     * @var string[]
     */
    private $containedSnippetCodes;

    /**
     * @param string $containerCode
     * @param string[] $containedSnippetCodes
     */
    public function __construct($containerCode, array $containedSnippetCodes)
    {
        $this->validateContainerCode($containerCode);
        // todo: validate contained snippet codes (via yet to be implemented snippet code value object)
        $this->containerCode = $containerCode;
        $this->containedSnippetCodes = $containedSnippetCodes;
    }

    /**
     * @param string $containerCode
     */
    private function validateContainerCode($containerCode)
    {
        if (!is_string($containerCode)) {
            throw new InvalidSnippetContainerCodeException('The snippet container code has to be a string');
        }
        if (strlen($containerCode) < 2) {
            $message = 'The snippet container code has to be at least 2 characters long';
            throw new InvalidSnippetContainerCodeException($message);
        }
    }

    /**
     * @param string $code
     * @param string[] $containedSnippetCodes
     * @return static
     */
    public static function rehydrate($code, array $containedSnippetCodes)
    {
        return new static($code, $containedSnippetCodes);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->containerCode;
    }

    /**
     * @return string[]
     */
    public function getSnippetCodes()
    {
        return $this->containedSnippetCodes;
    }

    /**
     * @return string[]
     */
    public function toArray()
    {
        return [$this->getCode() => $this->getSnippetCodes()];
    }
}
