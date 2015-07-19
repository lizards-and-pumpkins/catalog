<?php

namespace Brera\Content;

use Brera\Context\Context;
use Brera\InvalidSnippetCodeException;
use Brera\SnippetKeyGenerator;

class ContentBlockSnippetKeyGenerator implements SnippetKeyGenerator
{
    /**
     * @var string
     */
    private $snippetCode;

    /**
     * @var array
     */
    private $contextParts;

    /**
     * @param string $snippetCode
     */
    public function __construct($snippetCode, array $contextParts)
    {
        if (!is_string($snippetCode)) {
            throw new InvalidSnippetCodeException(sprintf(
                'The snippet code for the ContentBlockSnippetKeyGenerator has to be a string'
            ));
        }

        $this->snippetCode = $snippetCode;
        $this->contextParts = $contextParts;
    }
    
    /**
     * @param Context $context
     * @param string[] $data
     * @return string
     */
    public function getKeyForContext(Context $context, array $data)
    {
        if (!array_key_exists('content_block_id', $data)) {
            throw new MissingContentBlockIdException(sprintf(
                'Content block ID must be specified when getting a content block snippet key'
            ));
        }

        return sprintf(
            '%s_%s_%s',
            $this->snippetCode,
            $data['content_block_id'],
            $context->getIdForParts($this->contextParts)
        );
    }

    /**
     * @return string[]
     */
    public function getContextPartsUsedForKey()
    {
        return $this->contextParts;
    }
}
