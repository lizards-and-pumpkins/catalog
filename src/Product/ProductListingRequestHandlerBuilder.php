<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Brera\PageBuilder;
use Brera\SnippetKeyGeneratorLocator;

class ProductListingRequestHandlerBuilder
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var PageBuilder
     */
    private $pageBuilder;

    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $keyGeneratorLocator;

    public function __construct(
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $keyGeneratorLocator
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->pageBuilder = $pageBuilder;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
    }

    /**
     * @param HttpUrl $url
     * @param Context $context
     * @return ProductListingRequestHandler
     */
    public function create(HttpUrl $url, Context $context)
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductListingMetaInfoSnippetRenderer::CODE
        );
        $urlKey = ltrim($url->getPathRelativeToWebFront(), '/');
        $metaInfoSnippetKey = $keyGenerator->getKeyForContext($context, ['url_key' => $urlKey]);

        return new ProductListingRequestHandler(
            $metaInfoSnippetKey,
            $context,
            $this->dataPoolReader,
            $this->pageBuilder,
            $this->keyGeneratorLocator
        );
    }
}
