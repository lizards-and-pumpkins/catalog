<?php

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\SnippetCodeCanNotBeProcessedException;

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

    /**
     * {@inheritdoc}
     */
    public function canHandle($snippetCode)
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->canHandle($snippetCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeyGeneratorForSnippetCode($snippetCode)
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
