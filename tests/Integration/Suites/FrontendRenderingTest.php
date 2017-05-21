<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\Locale\Locale;
use LizardsAndPumpkins\Context\Website\IntegrationTestUrlToWebsiteMap;
use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\ProductDetail\ProductDetailViewRequestHandler;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\GenericPageBuilder;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\SelfContainedContextBuilder;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\ProductDetail\ProductDetailPageMetaInfoSnippetContent;
use LizardsAndPumpkins\ProductDetail\ProductDetailViewSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\DataPool\KeyGenerator\RegistrySnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;

class FrontendRenderingTest extends AbstractIntegrationTest
{
    private $testProductId = '333';

    /**
     * @var CatalogMasterFactory
     */
    private $factory;

    /**
     * @var RegistrySnippetKeyGeneratorLocatorStrategy
     */
    private $snippetKeyGeneratorLocator;

    /**
     * @var HttpRequest
     */
    private $request;

    private function addSnippetsFixtureToKeyValueStorage(string $productDetailPageMetaSnippetKey, Context $context)
    {
        $dataPoolWriter = $this->factory->createDataPoolWriter();

        $rootSnippetCode = 'root-snippet';
        $this->registerSnippetKeyGenerators($rootSnippetCode);
        
        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create(
            $this->testProductId,
            $rootSnippetCode,
            ['head', 'body'],
            []
        );
        $metaInfoSnippet = Snippet::create($productDetailPageMetaSnippetKey, json_encode($pageMetaInfo->getInfo()));

        $rootSnippetContent = '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>';
        $rootSnippet = Snippet::create($this->getSnippetKey($rootSnippetCode, $context), $rootSnippetContent);

        $pageSnippets = $this->createTestProductDetailPageSnippets($context);

        $dataPoolWriter->writeSnippets($rootSnippet, $metaInfoSnippet, ...$pageSnippets);
    }

    private function registerSnippetKeyGenerators(string $rootSnippetCode)
    {
        $rootSnippetKeyGenerator = new GenericSnippetKeyGenerator(
            ProductDetailViewSnippetRenderer::CODE,
            $this->factory->getRequiredContextParts(),
            [Product::ID]
        );
        $this->snippetKeyGeneratorLocator->register($rootSnippetCode, function () use ($rootSnippetKeyGenerator) {
            return $rootSnippetKeyGenerator;
        });
        $this->snippetKeyGeneratorLocator->register('head', function () {
            return new GenericSnippetKeyGenerator('head', $this->factory->getRequiredContextParts(), []);
        });
        $this->snippetKeyGeneratorLocator->register('body', function () {
            return new GenericSnippetKeyGenerator('body', $this->factory->getRequiredContextParts(), []);
        });
    }

    private function getSnippetKey(string $code, Context $context) : string
    {
        $keyGenerator = $this->snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($code);
        return $keyGenerator->getKeyForContext($context, [Product::ID => $this->testProductId]);
    }

    /**
     * @param Context $context
     * @return Snippet[]
     */
    private function createTestProductDetailPageSnippets(Context $context) : array
    {
        $headSnippetContent = '<title>Page Title</title>';
        $headSnippet = Snippet::create($this->getSnippetKey('head', $context), $headSnippetContent);

        $bodySnippetContent = '<h1>Headline</h1>';
        $bodySnippet = Snippet::create($this->getSnippetKey('body', $context), $bodySnippetContent);

        $productJsonSnippetKey = $this->getSnippetKey(ProductJsonSnippetRenderer::CODE, $context);
        $jsonSnippetContent = json_encode(['sku' => $this->testProductId]);
        $productJsonSnippet = Snippet::create($productJsonSnippetKey, $jsonSnippetContent);

        $priceSnippetKey = $this->getSnippetKey(PriceSnippetRenderer::PRICE, $context);
        $priceSnippetContent = '1199';
        $priceSnippet = Snippet::create($priceSnippetKey, $priceSnippetContent);

        $specialPriceSnippetKey = $this->getSnippetKey(PriceSnippetRenderer::SPECIAL_PRICE, $context);
        $specialPriceSnippetContent = '999';
        $specialPriceSnippet = Snippet::create($specialPriceSnippetKey, $specialPriceSnippetContent);
        
        return [$headSnippet, $bodySnippet, $productJsonSnippet, $priceSnippet, $specialPriceSnippet];
    }

    private function createDummyRequest(HttpUrl $url) : HttpRequest
    {
        return HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $url,
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
    }

    private function createProductDetailViewRequestHandler(
        Context $context,
        Logger $logger,
        SnippetKeyGenerator $productDetailPageMetaSnippetKeyGenerator
    ) : ProductDetailViewRequestHandler {
        $dataPoolReader = $this->factory->createDataPoolReader();

        return new ProductDetailViewRequestHandler(
            $context,
            $dataPoolReader,
            new GenericPageBuilder($dataPoolReader, $this->snippetKeyGeneratorLocator, $logger),
            new IntegrationTestUrlToWebsiteMap(),
            $this->factory->getTranslatorRegistry(),
            $productDetailPageMetaSnippetKeyGenerator
        );
    }

    protected function setUp()
    {
        $this->request = $this->createDummyRequest(HttpUrl::fromString('http://example.com/product1'));
        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($this->request);
        $this->snippetKeyGeneratorLocator = $this->factory->createRegistrySnippetKeyGeneratorLocatorStrategy();
    }

    public function testPageIsRenderedFromAnUrlWithoutVariablesInSnippets()
    {
        $context = SelfContainedContextBuilder::rehydrateContext([
            DataVersion::CONTEXT_CODE => '-1',
            Locale::CONTEXT_CODE => 'foo_BAR'
        ]);
        
        /** @var UrlToWebsiteMap $urlToWebsiteMap */
        $urlToWebsiteMap = $this->factory->createUrlToWebsiteMap();
        $metaSnippetKeyGenerator = $this->factory->createProductDetailPageMetaSnippetKeyGenerator();
        $pathWithoutWebsitePrefix = $urlToWebsiteMap->getRequestPathWithoutWebsitePrefix((string) $this->request->getUrl());
        $productDetailPageMetaSnippetKey = $metaSnippetKeyGenerator->getKeyForContext(
            $context,
            [PageMetaInfoSnippetContent::URL_KEY => $pathWithoutWebsitePrefix]
        );

        $this->addSnippetsFixtureToKeyValueStorage($productDetailPageMetaSnippetKey, $context);

        $logger = $this->factory->getLogger();

        $pageBuilder = $this->createProductDetailViewRequestHandler($context, $logger, $metaSnippetKeyGenerator);
        
        $page = $pageBuilder->process($this->request);
        
        $this->failIfMessagesWhereLogged($logger);

        $expected = '<html><head><title>Page Title</title></head><body><h1>Headline</h1></body></html>';

        $this->assertEquals($expected, $page->getBody());
    }
}
