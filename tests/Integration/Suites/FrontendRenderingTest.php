<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\Locale\Locale;
use LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator;
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
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\ProductDetail\ProductDetailPageMetaInfoSnippetContent;
use LizardsAndPumpkins\ProductDetail\ProductDetailMetaSnippetRenderer;
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

    private function addSnippetsFixtureToKeyValueStorage(string $productDetailPageMetaSnippetKey, Context $context)
    {
        $dataPoolWriter = $this->factory->createDataPoolWriter();

        $rootSnippetCode = 'root-snippet';
        $this->registerSnippetKeyGenerators($rootSnippetCode);
        
        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create(
            $this->testProductId,
            $rootSnippetCode,
            $pageSnippetCodes = ['head', 'body'],
            $containers = [],
            $pageSpecificData = []
        );
        $metaInfoSnippet = Snippet::create($productDetailPageMetaSnippetKey, json_encode($pageMetaInfo->toArray()));

        $rootSnippetContent = '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>';
        $rootSnippet = Snippet::create($this->getSnippetKey($rootSnippetCode, $context), $rootSnippetContent);

        $pageSnippets = $this->createTestProductDetailPageSnippets($context);

        $dataPoolWriter->writeSnippets($rootSnippet, $metaInfoSnippet, ...$pageSnippets);
    }

    private function registerSnippetKeyGenerators(string $rootSnippetCode)
    {
        $rootSnippetKeyGenerator = new GenericSnippetKeyGenerator(
            ProductDetailMetaSnippetRenderer::CODE,
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
        string $urlKey,
        Context $context
    ) : ProductDetailViewRequestHandler {
        $dataPoolReader = $this->factory->createDataPoolReader();
        $snippetReader = $this->factory->createSnippetReader();

        return new ProductDetailViewRequestHandler(
            $context,
            new GenericPageBuilder($dataPoolReader, $this->snippetKeyGeneratorLocator),
            $this->factory->getTranslatorRegistry(),
            $snippetReader->getPageMetaSnippet($urlKey, $context)
        );
    }

    public function testPageIsRenderedFromAnUrlWithoutVariablesInSnippets()
    {
        $urlKey = 'product1';
        $request = $this->createDummyRequest(HttpUrl::fromString('http://example.com/' . $urlKey));

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $this->snippetKeyGeneratorLocator = $this->factory->createRegistrySnippetKeyGeneratorLocatorStrategy();

        $context = SelfContainedContextBuilder::rehydrateContext([
            DataVersion::CONTEXT_CODE => '-1',
            Locale::CONTEXT_CODE => 'foo_BAR'
        ]);
        
        $urlToWebsiteMap = $this->factory->createUrlToWebsiteMap();
        $metaSnippetKeyGenerator = $this->factory->createProductDetailPageMetaSnippetKeyGenerator();
        $pathWithoutWebsitePrefix = $urlToWebsiteMap->getRequestPathWithoutWebsitePrefix((string) $request->getUrl());
        $productDetailPageMetaSnippetKey = $metaSnippetKeyGenerator->getKeyForContext(
            $context,
            [PageMetaInfoSnippetContent::URL_KEY => $pathWithoutWebsitePrefix]
        );

        $this->addSnippetsFixtureToKeyValueStorage($productDetailPageMetaSnippetKey, $context);

        $pageBuilder = $this->createProductDetailViewRequestHandler($urlKey, $context);

        $page = $pageBuilder->process($request);

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $expected = '<html><head><title>Page Title</title></head><body><h1>Headline</h1></body></html>';

        $this->assertEquals($expected, $page->getBody());
    }
}
