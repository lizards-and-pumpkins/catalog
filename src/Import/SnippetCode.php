<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Import\Exception\InvalidSnippetCodeException;

class SnippetCode implements \JsonSerializable
{
    /**
     * @var string
     */
    private $snippetCode;

    public function __construct(string $snippetCode)
    {
        $snippetCode = trim($snippetCode);

        if ($snippetCode === '') {
            throw new InvalidSnippetCodeException('Snippet code must not be empty.');
        }

        if (strlen($snippetCode) < 2) {
            throw new InvalidSnippetCodeException('The snippet container code has to be at least 2 characters long.');
        }

        $this->snippetCode = $snippetCode;
    }

    public function __toString(): string
    {
        return $this->snippetCode;
    }

    public function jsonSerialize(): string
    {
        return $this->snippetCode;
    }
}
