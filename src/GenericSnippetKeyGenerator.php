<?php


namespace Brera;

use Brera\Context\Context;

class GenericSnippetKeyGenerator implements SnippetKeyGenerator
{
    /**
     * @var
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
     * @param Context $context
     * @param array $data
     * @return string
     */
    public function getKeyForContext(Context $context, array $data = [])
    {
        return sprintf(
            '%s_%s',
            $this->snippetCode,
            $context->getIdForParts($this->getContextPartsUsedForKey())
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
     * @return string[]
     */
    public function getContextPartsUsedForKey()
    {
        return $this->contextParts;
    }
}
