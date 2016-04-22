<?php

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\InvalidSnippetCodeException;
use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\SnippetCodeCanNotBeProcessedException;
use LizardsAndPumpkins\Util\SnippetCodeValidator;

class RegistrySnippetKeyGeneratorLocatorStrategy implements SnippetKeyGeneratorLocator
{
    /**
     * @var \Closure[]
     */
    private $keyGeneratorFactoryClosures = [];

    /**
     * {@inheritdoc}
     */
    public function canHandle($snippetCode)
    {
        return array_key_exists($snippetCode, $this->keyGeneratorFactoryClosures);
    }

    /**
     * {@inheritdoc}
     */
    public function getKeyGeneratorForSnippetCode($snippetCode)
    {
        $this->validateSnippetCode($snippetCode);

        if (!$this->canHandle($snippetCode)) {
            throw new SnippetCodeCanNotBeProcessedException(
                sprintf('%s can not process "%s" snippet code.', __CLASS__, $snippetCode)
            );
        }

        return call_user_func($this->keyGeneratorFactoryClosures[$snippetCode]);
    }

    /**
     * @param string $snippetCode
     */
    private function validateSnippetCode($snippetCode)
    {
        SnippetCodeValidator::validate($snippetCode);
    }

    /**
     * @param string $snippetCode
     * @param \Closure $keyGeneratorFactoryClosure
     */
    public function register($snippetCode, \Closure $keyGeneratorFactoryClosure)
    {
        $this->validateSnippetCode($snippetCode);
        $this->keyGeneratorFactoryClosures[$snippetCode] = $keyGeneratorFactoryClosure;
    }
}
