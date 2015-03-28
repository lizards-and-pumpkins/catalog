<?php

namespace Brera\Product;

use Brera\AbstractHttpRequestHandler;
use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\Http\HttpUrl;
use Brera\Logger;
use Brera\PageMetaInfoSnippetContent;
use Brera\SnippetKeyGeneratorLocator;
use Brera\UrlPathKeyGenerator;

class ProductDetailViewRequestHandler extends AbstractHttpRequestHandler
{
    /**
     * @var ProductId
     */
    private $productId;

    /**
     * @var HttpUrl
     */
    private $httpUrl;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var string
     */
    private $pageMetaInfoSnippetKey;

    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $keyGeneratorLocator;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param string $pageMetaInfoSnippetKey
     * @param Context $context
     * @param SnippetKeyGeneratorLocator $keyGeneratorLocator
     * @param DataPoolReader $dataPoolReader
     * @param Logger $logger
     */
    public function __construct(
        $pageMetaInfoSnippetKey,
        Context $context,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        DataPoolReader $dataPoolReader,
        Logger $logger
    ) {
        $this->pageMetaInfoSnippetKey = $pageMetaInfoSnippetKey;
        $this->context = $context;
        $this->dataPoolReader = $dataPoolReader;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    final protected function getPageMetaInfoSnippetKey()
    {
        return $this->pageMetaInfoSnippetKey;
    }

    /**
     * @param string $snippetJson
     * @return PageMetaInfoSnippetContent
     */
    final protected function createPageMetaInfoInstance($snippetJson)
    {
        $metaInfo = PageMetaInfoSnippetContent::fromJson($snippetJson);
        $this->productId = $metaInfo->getSourceId();
        return $metaInfo;
    }

    /**
     * @param string $key
     * @return string
     */
    final protected function getSnippetKeyInContext($key)
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode($key);
        return $keyGenerator->getKeyForContext($this->productId, $this->context);
    }



    /**
     * @param string $snippetKey
     * @return string string
     */
    final protected function formatSnippetNotAvailableErrorMessage($snippetKey)
    {
        return sprintf(
            'Snippet not available (key "%s", product id "%s", context "%s")',
            $snippetKey,
            $this->productId,
            $this->context->getId()
        );
    }

    /**
     * @return DataPoolReader
     */
    final protected function getDataPoolReader()
    {
        return $this->dataPoolReader;
    }

    /**
     * @return Logger
     */
    final protected function getLogger()
    {
        return $this->logger;
    }
}
