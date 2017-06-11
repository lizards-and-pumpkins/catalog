<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\SnippetCodeCanNotBeProcessedException;
use LizardsAndPumpkins\Import\SnippetCode;

class CompositeSnippetKeyGeneratorLocatorStrategy implements SnippetKeyGeneratorLocator
{
    /**
     * @var SnippetKeyGeneratorLocator[]
     */
    private $strategies;

    public function __construct(SnippetKeyGeneratorLocator ...$strategies)
    {
        $this->strategies = $strategies;
    }

    public function canHandle(SnippetCode $snippetCode): bool
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->canHandle($snippetCode)) {
                return true;
            }
        }

        return false;
    }

    public function getKeyGeneratorForSnippetCode(SnippetCode $snippetCode): SnippetKeyGenerator
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->canHandle($snippetCode)) {
                return $strategy->getKeyGeneratorForSnippetCode($snippetCode);
            }
        }

        throw new SnippetCodeCanNotBeProcessedException(
            sprintf('No snippet key generator is found for snippet code "%s"', $snippetCode)
        );
    }
}
