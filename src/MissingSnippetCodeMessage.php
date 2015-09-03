<?php

namespace Brera;

use Brera\Log\LogMessage;

class MissingSnippetCodeMessage implements LogMessage
{
    /**
     * @var string[]
     */
    private $missingSnippetCodes;

    /**
     * @var mixed[]
     */
    private $context;

    /**
     * @param string[] $missingSnippetCodes
     * @param mixed[] $context
     */
    public function __construct(array $missingSnippetCodes, array $context = [])
    {
        $this->missingSnippetCodes = $missingSnippetCodes;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'Snippets contained in the page meta information where not loaded from the data pool (%s)',
            implode(', ', $this->missingSnippetCodes)
        );
    }

    /**
     * @return mixed[]
     */
    public function getContext()
    {
        return $this->context;
    }
}
