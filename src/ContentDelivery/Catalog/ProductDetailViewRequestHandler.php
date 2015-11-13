<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyValue\Exception\KeyNotFoundException;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Exception\UnableToHandleRequestException;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductDetailPageMetaInfoSnippetContent;
use LizardsAndPumpkins\SnippetKeyGenerator;

class ProductDetailViewRequestHandler implements HttpRequestHandler
{
    /**
     * @var ProductDetailPageMetaInfoSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var string
     */
    private $requestObjectHash;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var PageBuilder
     */
    private $pageBuilder;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGenerator $snippetKeyGenerator
    ) {
        $this->context = $context;
        $this->dataPoolReader = $dataPoolReader;
        $this->pageBuilder = $pageBuilder;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
    }

    /**
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request)
    {
        $this->loadPageMetaInfoSnippet($request);
        return (bool)$this->pageMetaInfo;
    }

    /**
     * @param HttpRequest $request
     * @return HttpResponse
     */
    public function process(HttpRequest $request)
    {
        if (!$this->canProcess($request)) {
            throw new UnableToHandleRequestException;
        }

        $keyGeneratorParams = [
            Product::ID => $this->pageMetaInfo->getProductId()
        ];

        return $this->pageBuilder->buildPage($this->pageMetaInfo, $this->context, $keyGeneratorParams);
    }

    private function loadPageMetaInfoSnippet(HttpRequest $request)
    {
        $requestObjectHash = spl_object_hash($request);

        if ($requestObjectHash !== $this->requestObjectHash) {
            $this->requestObjectHash = $requestObjectHash;
            $this->pageMetaInfo = false;
            $metaInfoSnippetKey = $this->getMetaInfoSnippetKey($request);
            $json = $this->getPageMetaInfoJsonIfExists($metaInfoSnippetKey);
            if ($json) {
                $this->pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::fromJson($json);
            }
        }
    }

    /**
     * @param string $metaInfoSnippetKey
     * @return string
     */
    private function getPageMetaInfoJsonIfExists($metaInfoSnippetKey)
    {
        try {
            $snippet = $this->dataPoolReader->getSnippet($metaInfoSnippetKey);
        } catch (KeyNotFoundException $e) {
            $snippet = '';
        }
        return $snippet;
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getMetaInfoSnippetKey(HttpRequest $request)
    {
        $urlKey = $request->getUrlPathRelativeToWebFront();
        $metaInfoSnippetKey = $this->snippetKeyGenerator->getKeyForContext(
            $this->context,
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );

        return $metaInfoSnippetKey;
    }
}
