<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Brera\Logger;
use Brera\PageBuilder;
use Brera\SnippetKeyGenerator;
use Brera\SnippetKeyGeneratorLocator;
use Brera\UrlPathKeyGenerator;

class ProductDetailViewRequestHandlerBuilder
{
    /**
     * @var UrlPathKeyGenerator
     */
    private $urlPathKeyGenerator;

    private $dataPoolReader;

    /**
     * @var PageBuilder
     */
    private $pageBuilder;

    public function __construct(
        UrlPathKeyGenerator $urlPathKeyGenerator,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder
    ) {
        $this->urlPathKeyGenerator = $urlPathKeyGenerator;
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
        return new ProductDetailViewRequestHandler(
            $this->urlPathKeyGenerator->getUrlKeyForUrlInContext($url, $context),
            $context,
            $this->dataPoolReader,
            $this->pageBuilder
        );
    }
}
