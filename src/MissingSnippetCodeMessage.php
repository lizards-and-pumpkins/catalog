<?php

namespace Brera;

class MissingSnippetCodeMessage implements LogMessage
{
    /**
     * @var string[]
     */
    private $missingSnippetCodes;

    /**
     * @param string[] $missingSnippetCodes
     */
    public function __construct(array $missingSnippetCodes)
    {
        $this->missingSnippetCodes = $missingSnippetCodes;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return sprintf(
            'Snippets listed in the page meta information where not loaded from the data pool (%s)',
            implode(', ', $this->missingSnippetCodes)
        );
    }
}
