<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\SnippetCodeCanNotBeProcessedException;
use LizardsAndPumpkins\Import\SnippetCode;

class RegistrySnippetKeyGeneratorLocatorStrategy implements SnippetKeyGeneratorLocator
{
    /**
     * @var \Closure[]
     */
    private $keyGeneratorFactoryClosures = [];

    public function canHandle(SnippetCode $snippetCode): bool
    {
        return array_key_exists((string) $snippetCode, $this->keyGeneratorFactoryClosures);
    }

    public function getKeyGeneratorForSnippetCode(SnippetCode $snippetCode): SnippetKeyGenerator
    {
        if (! $this->canHandle($snippetCode)) {
            throw new SnippetCodeCanNotBeProcessedException(
                sprintf('%s can not process "%s" snippet code.', __CLASS__, $snippetCode)
            );
        }

        return call_user_func($this->keyGeneratorFactoryClosures[(string) $snippetCode]);
    }

    public function register(SnippetCode $snippetCode, \Closure $keyGeneratorFactoryClosure)
    {
        $this->keyGeneratorFactoryClosures[(string) $snippetCode] = $keyGeneratorFactoryClosure;
    }
}
