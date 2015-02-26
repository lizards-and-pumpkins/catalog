<?php


namespace Brera;

class SnippetKeyGeneratorLocator
{
    /**
     * @param string $snippetCode
     * @return GenericSnippetKeyGenerator
     */
    public function getKeyGeneratorForSnippetCode($snippetCode)
    {
        $this->validateSnippetCode($snippetCode);
        return new GenericSnippetKeyGenerator($snippetCode);
    }

    /**
     * @param string $snippetCode
     * @throws InvalidSnippetCodeException
     */
    private function validateSnippetCode($snippetCode)
    {
        if (!is_string($snippetCode)) {
            throw new InvalidSnippetCodeException(sprintf(
                'Expected snippet code to be a string but got "%s"',
                (is_scalar($snippetCode) ? $snippetCode : gettype($snippetCode))
            ));
        }
    }
}
