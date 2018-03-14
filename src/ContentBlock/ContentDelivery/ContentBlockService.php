<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ContentBlock\ContentDelivery;

use LizardsAndPumpkins\ContentBlock\ContentDelivery\Exception\ContentBlockNotFoundException;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;

class ContentBlockService
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $snippetKeyGeneratorLocator;

    public function __construct(DataPoolReader $dataPoolReader, SnippetKeyGeneratorLocator $snippetKeyGeneratorLocator)
    {
        $this->dataPoolReader = $dataPoolReader;
        $this->snippetKeyGeneratorLocator = $snippetKeyGeneratorLocator;
    }

    public function getContentBlock(string $contentBlockName, Context $context): string
    {
        try {
            $snippetKeyGenerator = $this->snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($contentBlockName);
            $key = $snippetKeyGenerator->getKeyForContext($context, []);

            return $this->dataPoolReader->getSnippet($key);
        } catch (KeyNotFoundException $e) {
            throw new ContentBlockNotFoundException(sprintf('Content block "%s" does not exist.', $contentBlockName));
        }
    }
}
