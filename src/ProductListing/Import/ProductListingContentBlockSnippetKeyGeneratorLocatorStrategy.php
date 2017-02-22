<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\SnippetCodeCanNotBeProcessedException;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;

class ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy implements SnippetKeyGeneratorLocator
{
    const LISTING_CONTENT_BLOCK_KEY_PREFIX = 'product_listing_content_block_';

    /**
     * @var \Closure
     */
    private $contentBlockKeyGeneratorClosure;

    public function __construct(\Closure $contentBlockKeyGeneratorClosure)
    {
        $this->contentBlockKeyGeneratorClosure = $contentBlockKeyGeneratorClosure;
    }

    public function canHandle(string $snippetCode) : bool
    {
        return strpos($snippetCode, self::LISTING_CONTENT_BLOCK_KEY_PREFIX) === 0;
    }

    public function getKeyGeneratorForSnippetCode(string $snippetCode) : SnippetKeyGenerator
    {
        if (!$this->canHandle($snippetCode)) {
            throw new SnippetCodeCanNotBeProcessedException(
                sprintf('%s can not process "%s" snippet code.', __CLASS__, $snippetCode)
            );
        }

        return call_user_func($this->contentBlockKeyGeneratorClosure, $snippetCode);
    }
}
