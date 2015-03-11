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
        if (! is_string($snippetCode)) {
            throw new InvalidSnippetCodeException(sprintf(
                'The snippet code has to be a string, got "%s"',
                $this->getSnippetCodeRepresentationForErrorMessage($snippetCode)
            ));
        }
        $this->snippetCode = $snippetCode;
        $this->contextParts = $contextParts;
    }
    
    /**
     * @param mixed $identifier
     * @param Context $context
     * @return string
     */
    public function getKeyForContext($identifier, Context $context)
    {
        return sprintf(
            '%s_%s_%s',
            $this->snippetCode,
            $this->getStringRepresentationOfIdentifier($identifier),
            $context->getId()
        );
    }

    /**
     * @param string $snippetCode
     * @return string
     */
    private function getSnippetCodeRepresentationForErrorMessage($snippetCode)
    {
        return is_scalar($snippetCode) ?
            (string) $snippetCode :
            gettype($snippetCode);
    }

    /**
     * @param mixed $identifier
     * @return string
     */
    private function getStringRepresentationOfIdentifier($identifier)
    {
        return (string) $identifier;
    }

    /**
     * @return string[]
     */
    public function getContextParts()
    {
        return $this->contextParts;
    }
}
