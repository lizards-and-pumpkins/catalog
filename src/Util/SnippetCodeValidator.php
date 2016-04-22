<?php

namespace LizardsAndPumpkins\Util;

use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;

class SnippetCodeValidator
{
    public static function validate($snippetCode)
    {
        if (! is_string($snippetCode)) {
            throw new InvalidSnippetCodeException(
                sprintf('Snippet code must be string, "%s" passed.', gettype($snippetCode))
            );
        }

        if (trim($snippetCode) === '') {
            throw new InvalidSnippetCodeException('Snippet code must not be empty.');
        }
    }
}
