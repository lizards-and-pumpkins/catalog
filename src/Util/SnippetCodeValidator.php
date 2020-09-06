<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util;

use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;

class SnippetCodeValidator
{
    public static function validate(string $snippetCode): void
    {
        if (trim($snippetCode) === '') {
            throw new InvalidSnippetCodeException('Snippet code must not be empty.');
        }
    }
}
