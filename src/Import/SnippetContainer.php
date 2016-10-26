<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Import\Exception\InvalidSnippetContainerCodeException;

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
    public function __construct(string $containerCode, array $containedSnippetCodes)
    {
        $this->validateContainerCode($containerCode);
        // todo: validate contained snippet codes (via yet to be implemented snippet code value object)
        $this->containerCode = $containerCode;
        $this->containedSnippetCodes = $containedSnippetCodes;
    }

    private function validateContainerCode(string $containerCode)
    {
        if (strlen($containerCode) < 2) {
            $message = 'The snippet container code has to be at least 2 characters long';
            throw new InvalidSnippetContainerCodeException($message);
        }
    }

    /**
     * @param string $code
     * @param string[] $containedSnippetCodes
     * @return SnippetContainer
     */
    public static function rehydrate(string $code, array $containedSnippetCodes) : SnippetContainer
    {
        return new static($code, $containedSnippetCodes);
    }

    public function getCode() : string
    {
        return $this->containerCode;
    }

    /**
     * @return string[]
     */
    public function getSnippetCodes() : array
    {
        return $this->containedSnippetCodes;
    }

    /**
     * @return string[]
     */
    public function toArray() : array
    {
        return [$this->getCode() => $this->getSnippetCodes()];
    }
}
