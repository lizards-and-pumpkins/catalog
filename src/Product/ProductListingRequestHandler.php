<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\Http\AbstractHttpRequestHandler;
use Brera\Logger;
use Brera\SnippetKeyGeneratorLocator;

class ProductListingRequestHandler extends AbstractHttpRequestHandler
{
    /**
     * @var string
     */
    private $selectionCriteria;

    /**
     * @var Context
     */
    private $context;

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
        $this->context = $context;
        $this->pageMetaInfoSnippetKey = $pageMetaInfoSnippetKey;
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
     * @return ProductListingMetaInfoSnippetContent
     */
    final protected function createPageMetaInfoInstance($snippetJson)
    {
        $metaInfo = ProductListingMetaInfoSnippetContent::fromJson($snippetJson);
        $this->selectionCriteria = $metaInfo->getSelectionCriteria();
        return $metaInfo;
    }

    /**
     * @param string $snippetCode
     * @return string
     */
    final protected function getSnippetKey($snippetCode)
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);
        $params = ['selection_criteria' => $this->selectionCriteria];
        return $keyGenerator->getKeyForContext($this->context, $params);
    }

    /**
     * @param string $snippetKey
     * @return string string
     */
    final protected function formatSnippetNotAvailableErrorMessage($snippetKey)
    {
        return sprintf(
            'Snippet not available (key "%s", listing type id "%s", context "%s")',
            $snippetKey,
            implode('|', $this->selectionCriteria),
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
