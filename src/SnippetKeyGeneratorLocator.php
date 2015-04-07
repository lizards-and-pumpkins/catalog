<?php


namespace Brera;

class SnippetKeyGeneratorLocator
{
    /**
     * @var SnippetKeyGenerator[]
     */
    private $keyGenerators = [];

    /**
     * @param string $snippetCode
     * @return GenericSnippetKeyGenerator
     */
    public function getKeyGeneratorForSnippetCode($snippetCode)
    {
        $this->validateSnippetCode($snippetCode);
        $this->validateKeyGeneratorForSnippetIsKnown($snippetCode);
        return $this->keyGenerators[$snippetCode];
    }

    /**
     * @param string $snippetCode
     * @throws InvalidSnippetCodeException
     * @throws SnippetKeyGeneratorNotRegisteredException
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

    /**
     * @param string $snippetCode
     */
    private function validateKeyGeneratorForSnippetIsKnown($snippetCode)
    {
        if (! array_key_exists($snippetCode, $this->keyGenerators)) {
            throw new SnippetKeyGeneratorNotRegisteredException(sprintf(
                'No key generator set for snippet "%s"',
                $snippetCode
            ));
        }
    }

    /**
     * @param string $snippetCode
     * @param SnippetKeyGenerator $snippetKeyGenerator
     */
    public function register($snippetCode, SnippetKeyGenerator $snippetKeyGenerator)
    {
        $this->validateSnippetCode($snippetCode);
        $this->keyGenerators[$snippetCode] = $snippetKeyGenerator;
    }
}
