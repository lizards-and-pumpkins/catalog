<?php

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

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
        SnippetCodeValidator::validate($snippetCode);

        if (!$this->canHandle($snippetCode)) {
            throw new SnippetCodeCanNotBeProcessedException(
                sprintf('%s can not process "%s" snippet code.', __CLASS__, $snippetCode)
            );
        }

        return call_user_func($this->keyGeneratorFactoryClosures[$snippetCode]);
    }

    /**
     * @param string $snippetCode
     * @param \Closure $keyGeneratorFactoryClosure
     */
    public function register($snippetCode, \Closure $keyGeneratorFactoryClosure)
    {
        SnippetCodeValidator::validate($snippetCode);
        $this->keyGeneratorFactoryClosures[$snippetCode] = $keyGeneratorFactoryClosure;
    }
}
