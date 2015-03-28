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

class ProductListingRequestHandler extends AbstractHttpRequestHandler
{
    /**
     * @var string
     */
    private $listingTypeId;

    /**
     * @var HttpUrl
     */
    private $httpUrl;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var UrlPathKeyGenerator
     */
    private $urlPathKeyGenerator;

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

    public function __construct(
        HttpUrl $url,
        Context $context,
        UrlPathKeyGenerator $urlPathKeyGenerator,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        DataPoolReader $dataPoolReader,
        Logger $logger
    ) {
        $this->httpUrl = $url;
        $this->context = $context;
        $this->urlPathKeyGenerator = $urlPathKeyGenerator;
        $this->dataPoolReader = $dataPoolReader;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    final protected function getPageMetaInfoSnippetKey()
    {
        return $this->urlPathKeyGenerator->getUrlKeyForUrlInContext($this->httpUrl, $this->context);
    }

    /**
     * @param string $key
     * @return string
     */
    final protected function getSnippetKeyInContext($key)
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode($key);
        return $keyGenerator->getKeyForContext($this->listingTypeId, $this->context);
    }

    /**
     * @param string $snippetJson
     * @return PageMetaInfoSnippetContent
     */
    final protected function createPageMetaInfoInstance($snippetJson)
    {
        $metaInfo = PageMetaInfoSnippetContent::fromJson($snippetJson);
        $this->listingTypeId = $metaInfo->getSourceId();
        return $metaInfo;
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
            $this->listingTypeId,
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
