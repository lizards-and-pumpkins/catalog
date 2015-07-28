<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Brera\PageBuilder;
use Brera\SnippetKeyGenerator;

class ProductDetailViewRequestHandlerBuilder
{
    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var PageBuilder
     */
    private $pageBuilder;

    public function __construct(
        SnippetKeyGenerator $snippetKeyGenerator,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder
    ) {
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->dataPoolReader = $dataPoolReader;
        $this->pageBuilder = $pageBuilder;
    }

    /**
     * @param HttpUrl $url
     * @param Context $context
     * @return ProductDetailViewRequestHandler
     */
    public function create(HttpUrl $url, Context $context)
    {
        $urlKey = ltrim($url->getPathRelativeToWebFront(), '/');
        $metaInfoSnippetKey = $this->snippetKeyGenerator->getKeyForContext($context, ['url_key' => $urlKey]);

        return new ProductDetailViewRequestHandler(
            $metaInfoSnippetKey,
            $context,
            $this->dataPoolReader,
            $this->pageBuilder
        );
    }
}
