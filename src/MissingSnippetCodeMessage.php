<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Log\LogMessage;

class MissingSnippetCodeMessage implements LogMessage
{
    /**
     * @var string[]
     */
    private $missingSnippetCodes;

    /**
     * @var Context
     */
    private $context;

    /**
     * @param string[] $missingSnippetCodes
     * @param Context $context
     */
    public function __construct(array $missingSnippetCodes, Context $context)
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
        return ['context' => $this->context];
    }

    /**
     * @return string
     */
    public function getContextSynopsis()
    {
        return sprintf('Context: %s', $this->context);
    }
}
