<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Brera\Logger;
use Brera\SnippetKeyGenerator;
use Brera\SnippetKeyGeneratorLocator;
use Brera\UrlPathKeyGenerator;

class ProductListingRequestHandlerBuilder
{
    /**
     * @var UrlPathKeyGenerator
     */
    private $urlPathKeyGenerator;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;
    
    /**
     * @var SnippetKeyGenerator
     */
    private $keyGeneratorLocator;
    
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        UrlPathKeyGenerator $urlPathKeyGenerator,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        DataPoolReader $dataPoolReader,
        Logger $logger
    ) {
        $this->urlPathKeyGenerator = $urlPathKeyGenerator;
        $this->dataPoolReader = $dataPoolReader;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->logger = $logger;
    }

    public function create(HttpUrl $url, Context $context)
    {
        return new ProductListingRequestHandler(
            $url,
            $context,
            $this->urlPathKeyGenerator,
            $this->keyGeneratorLocator,
            $this->dataPoolReader,
            $this->logger
        );
    }
}
