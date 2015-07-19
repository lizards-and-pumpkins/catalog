<?php

namespace Brera;

use Brera\Context\Context;

class GenericSnippetKeyGenerator implements SnippetKeyGenerator
{
    /**
     * @var string
     */
    private $snippetCode;
    
    /**
     * @var string[]
     */
    private $contextParts;

    /**
     * @param string $snippetCode
     * @param string[] $contextParts
     */
    public function __construct($snippetCode, array $contextParts)
    {
        if (!is_string($snippetCode)) {
            throw new InvalidSnippetCodeException(
                sprintf('The snippet code has to be a string, got "%s"', gettype($snippetCode))
            );
        }

        $this->snippetCode = $snippetCode;
        $this->contextParts = $contextParts;
    }

    /**
     * @param Context $context
     * @param mixed[] $data
     * @return string
     */
    public function getKeyForContext(Context $context, array $data)
    {
        $snippetKey = $this->snippetCode;

        if (!empty($data)) {
            $snippetKey .= '_' . implode('_', $data);
        }

        $snippetKey .= '_' . $context->getIdForParts($this->contextParts);

        return $snippetKey;
    }

    /**
     * @return string[]
     */
    public function getContextPartsUsedForKey()
    {
        return $this->contextParts;
    }
}
