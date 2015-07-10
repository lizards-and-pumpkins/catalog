<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\KeyNotFoundException;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpResponse;
use Brera\Http\UnableToHandleRequestException;
use Brera\PageBuilder;

class ProductDetailViewRequestHandler implements HttpRequestHandler
{
    /**
     * @var ProductDetailPageMetaInfoSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var string
     */
    private $metaInfoSnippetKey;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var PageBuilder
     */
    private $pageBuilder;

    /**
     * @param string $metaInfoSnippetKey
     * @param Context $context
     * @param DataPoolReader $dataPoolReader
     * @param PageBuilder $pageBuilder
     */
    public function __construct(
        $metaInfoSnippetKey,
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->metaInfoSnippetKey = ProductDetailViewInContextSnippetRenderer::CODE . '_' . $metaInfoSnippetKey;
        $this->context = $context;
        $this->pageBuilder = $pageBuilder;
    }

    /**
     * @return bool
     */
    public function canProcess()
    {
        $this->loadPageMetaInfoSnippet();
        return (bool)$this->pageMetaInfo;
    }

    /**
     * @param HttpRequest $request
     * @return HttpResponse
     * @throws UnableToHandleRequestException
     */
    public function process(HttpRequest $request)
    {
        if (!$this->canProcess()) {
            throw new UnableToHandleRequestException;
        }
        return $this->pageBuilder->buildPage(
            $this->pageMetaInfo,
            $this->context,
            $this->getPageParamArray()
        );
    }

    private function loadPageMetaInfoSnippet()
    {
        if (is_null($this->pageMetaInfo)) {
            $this->pageMetaInfo = false;
            $json = $this->getPageMetaInfoJsonIfExists();
            if ($json) {
                $this->pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::fromJson($json);
            }
        }
    }

    /**
     * @return string
     */
    private function getPageMetaInfoJsonIfExists()
    {
        try {
            $snippet = $this->dataPoolReader->getSnippet($this->metaInfoSnippetKey);
        } catch (KeyNotFoundException $e) {
            $snippet = '';
        }
        return $snippet;
    }

    /**
     * @return string[]
     */
    private function getPageParamArray()
    {
        return [
            ProductDetailPageMetaInfoSnippetContent::KEY_PRODUCT_ID => $this->pageMetaInfo->getProductId()
        ];
    }
}
