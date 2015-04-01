<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\Http\AbstractHttpRequestHandler;
use Brera\Logger;
use Brera\SnippetKeyGeneratorLocator;

class ProductDetailViewRequestHandler extends AbstractHttpRequestHandler
{
    /**
     * @var ProductId
     */
    private $productId;

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
     * @param string $snippetJson
     * @return ProductDetailPageMetaInfoSnippetContent
     */
    final protected function createPageMetaInfoInstance($snippetJson)
    {
        $metaInfo = ProductDetailPageMetaInfoSnippetContent::fromJson($snippetJson);
        $this->productId = $metaInfo->getProductId();
        return $metaInfo;
    }

    /**
     * @param string $snippetCode
     * @return string
     */
    final protected function getSnippetKey($snippetCode)
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);
        return $keyGenerator->getKeyForContext($this->context, ['product_id' => $this->productId]);
    }

    /**
     * @return string
     */
    final protected function getPageMetaInfoSnippetKey()
    {
        return $this->pageMetaInfoSnippetKey;
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
