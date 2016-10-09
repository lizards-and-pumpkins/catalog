<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\SnippetCodeCanNotBeProcessedException;
use LizardsAndPumpkins\Util\SnippetCodeValidator;

class RegistrySnippetKeyGeneratorLocatorStrategy implements SnippetKeyGeneratorLocator
{
    /**
     * @var \Closure[]
     */
    private $keyGeneratorFactoryClosures = [];

    public function canHandle(string $snippetCode) : bool
    {
        return array_key_exists($snippetCode, $this->keyGeneratorFactoryClosures);
    }

    public function getKeyGeneratorForSnippetCode(string $snippetCode) : SnippetKeyGenerator
    {
        SnippetCodeValidator::validate($snippetCode);

        if (!$this->canHandle($snippetCode)) {
            throw new SnippetCodeCanNotBeProcessedException(
                sprintf('%s can not process "%s" snippet code.', __CLASS__, $snippetCode)
            );
        }

        return call_user_func($this->keyGeneratorFactoryClosures[$snippetCode]);
    }

    public function register(string $snippetCode, \Closure $keyGeneratorFactoryClosure)
    {
        SnippetCodeValidator::validate($snippetCode);
        $this->keyGeneratorFactoryClosures[$snippetCode] = $keyGeneratorFactoryClosure;
    }
}
