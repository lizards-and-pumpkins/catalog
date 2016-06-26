<?php

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\Locale\Locale;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\UnableToHandleRequestException;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\Product\ProductDTO;
use LizardsAndPumpkins\Translation\TranslatorRegistry;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;

class ProductDetailViewRequestHandler implements HttpRequestHandler
{
    /**
     * @var ProductDetailPageMetaInfoSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var HttpRequest
     */
    private $requestObject;

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

    /**
     * @var TranslatorRegistry
     */
    private $translatorRegistry;

    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        TranslatorRegistry $translatorRegistry,
        SnippetKeyGenerator $snippetKeyGenerator
    ) {
        $this->context = $context;
        $this->dataPoolReader = $dataPoolReader;
        $this->pageBuilder = $pageBuilder;
        $this->translatorRegistry = $translatorRegistry;
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
            throw new UnableToHandleRequestException(sprintf('Unable to handle request to "%s"', $request->getUrl()));
        }

        $keyGeneratorParams = [
            ProductDTO::ID => $this->pageMetaInfo->getProductId(),
            'robots'       => 'all'
        ];

        $this->addRobotsMetaTagToHeadContainer();
        $this->addTranslationsToPageBuilder($this->context);

        return $this->pageBuilder->buildPage($this->pageMetaInfo, $this->context, $keyGeneratorParams);
    }

    private function loadPageMetaInfoSnippet(HttpRequest $request)
    {
        if ($request !== $this->requestObject) {
            $this->requestObject = $request;
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
        $urlKey = $request->getPathWithoutWebsitePrefix();
        $metaInfoSnippetKey = $this->snippetKeyGenerator->getKeyForContext(
            $this->context,
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );

        return $metaInfoSnippetKey;
    }

    private function addTranslationsToPageBuilder(Context $context)
    {
        $translator = $this->translatorRegistry->getTranslator(
            ProductDetailViewSnippetRenderer::CODE,
            $context->getValue(Locale::CONTEXT_CODE)
        );
        $this->addDynamicSnippetToPageBuilder('translations', json_encode($translator));
    }

    /**
     * @param string $snippetCode
     * @param string $snippetContents
     */
    private function addDynamicSnippetToPageBuilder($snippetCode, $snippetContents)
    {
        $snippetCodeToKeyMap = [$snippetCode => $snippetCode];
        $snippetKeyToContentMap = [$snippetCode => $snippetContents];

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }

    private function addRobotsMetaTagToHeadContainer()
    {
        $snippetCode = ProductDetailPageRobotsMetaTagSnippetRenderer::CODE;
        $this->pageBuilder->addSnippetToContainer('head_container', $snippetCode);
    }
}
