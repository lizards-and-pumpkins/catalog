<?php


namespace Brera;

use Brera\Context\Context;

class GenericSnippetKeyGenerator implements SnippetKeyGenerator
{
    /**
     * @param string $snippetCode
     * @param mixed $identifier
     * @param Context $context
     * @return string
     * @throws InvalidSnippetCodeException
     */
    public function getKeyForContext($snippetCode, $identifier, Context $context)
    {
        if (! is_string($snippetCode)) {
            throw new InvalidSnippetCodeException(sprintf(
                'The snippet code has to be a string, got "%s"',
                $this->getSnippetCodeRepresentationForErrorMessage($snippetCode)
            ));
        }

        return sprintf(
            '%s_%s_%s',
            $snippetCode,
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
}
