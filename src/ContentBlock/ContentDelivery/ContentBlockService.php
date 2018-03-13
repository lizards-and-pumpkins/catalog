<?php
declare(strict_types=1);

namespace LizardsAndPumpkins\ContentBlock\ContentDelivery;

use LizardsAndPumpkins\ContentBlock\ContentDelivery\Exception\ContentBlockNotFoundException;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;

/**
 * Class ContentBlockService
 *
 * @package LizardsAndPumpkins\ContentBlock\ContentDelivery
 */
class ContentBlockService
{
    const SNIPPET_KEY = 'category_list';

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $snippetKeyGeneratorLocator;

    /**
     * CategoryService constructor.
     *
     * @param DataPoolReader             $dataPoolReader
     * @param SnippetKeyGeneratorLocator $snippetKeyGeneratorLocator
     */
    public function __construct(DataPoolReader $dataPoolReader, SnippetKeyGeneratorLocator $snippetKeyGeneratorLocator)
    {
        $this->dataPoolReader = $dataPoolReader;
        $this->snippetKeyGeneratorLocator = $snippetKeyGeneratorLocator;
    }

    /**
     * @param string  $contentBlockName
     * @param Context $context
     *
     * @return string
     */
    public function getContentBlock(string $contentBlockName, Context $context): string
    {
        try {
            $snippetKeyGenerator = $this->snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode('content_block_'.$contentBlockName);
            $key = $snippetKeyGenerator->getKeyForContext($context, []);

            return $this->dataPoolReader->getSnippet($key);
        } catch (KeyNotFoundException $e) {
            throw new ContentBlockNotFoundException("Content block $contentBlockName does not exist");
        }
    }
}