<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\VersionedContext;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductDetailPageMetaInfoSnippetContent;
use LizardsAndPumpkins\Product\ProductDetailViewInContextSnippetRenderer;
use LizardsAndPumpkins\Product\ProductDetailViewRequestHandler;

class FrontendRenderingTest extends AbstractIntegrationTest
{
    private $testProductId = 333;

    /**
     * @var SampleMasterFactory
     */
    private $factory;

    /**
     * @param SnippetKeyGeneratorLocator $snippetKeyGeneratorLocator
     * @param string $productDetailPageMetaSnippetKey
     * @param Context $context
     */
    private function addPageMetaInfoFixtureToKeyValueStorage(
        SnippetKeyGeneratorLocator $snippetKeyGeneratorLocator,
        $productDetailPageMetaSnippetKey,
        Context $context
    ) {
        $dataPoolWriter = $this->factory->createDataPoolWriter();

        $rootSnippetCode = 'root-snippet';
        $rootSnippetKeyGenerator = new GenericSnippetKeyGenerator(
            ProductDetailViewInContextSnippetRenderer::CODE,
            $this->factory->getRequiredContexts(),
            [Product::ID]
        );
        $snippetKeyGeneratorLocator->register($rootSnippetCode, $rootSnippetKeyGenerator);
        $snippetKeyGeneratorLocator->register(
            'head',
            new GenericSnippetKeyGenerator('head', $this->factory->getRequiredContexts(), [])
        );
        $snippetKeyGeneratorLocator->register(
            'body',
            new GenericSnippetKeyGenerator('body', $this->factory->getRequiredContexts(), [])
        );

        $pageSnippet = Snippet::create(
            $rootSnippetKeyGenerator->getKeyForContext($context, [Product::ID => $this->testProductId]),
            '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>'
        );
        $dataPoolWriter->writeSnippet($pageSnippet);

        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create(
            $this->testProductId,
            $rootSnippetCode,
            [$rootSnippetCode, 'head', 'body']
        );
        $metaInfoSnippet = Snippet::create($productDetailPageMetaSnippetKey, json_encode($pageMetaInfo->getInfo()));
        $dataPoolWriter->writeSnippet($metaInfoSnippet);

        $headSnippetKeyGenerator = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode('head');
        $key = $headSnippetKeyGenerator->getKeyForContext($context, [Product::ID => $this->testProductId]);
        $headSnippet = Snippet::create($key, '<title>Page Title</title>');
        $dataPoolWriter->writeSnippet($headSnippet);

        $bodySnippetKeyGenerator = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode('body');
        $key = $bodySnippetKeyGenerator->getKeyForContext($context, [Product::ID => $this->testProductId]);
        $bodySnippet = Snippet::create($key, '<h1>Headline</h1>');
        $dataPoolWriter->writeSnippet($bodySnippet);
    }

    public function testPageIsRenderedFromAnUrlWithoutVariablesInSnippets()
    {
        $url = HttpUrl::fromString('http://example.com/product1');
        $urlKey = $url->getPathRelativeToWebFront();
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $url,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );
        
        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        
        $context = new VersionedContext(DataVersion::fromVersionString('1'));
        $snippetKeyGeneratorLocator = $this->factory->getSnippetKeyGeneratorLocator();
        $productDetailPageMetaSnippetKeyGenerator = $this->factory->createProductDetailPageMetaSnippetKeyGenerator();
        $productDetailPageMetaSnippetKey = $productDetailPageMetaSnippetKeyGenerator->getKeyForContext(
            $context,
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );


        $this->addPageMetaInfoFixtureToKeyValueStorage(
            $snippetKeyGeneratorLocator,
            $productDetailPageMetaSnippetKey,
            $context
        );

        $dataPoolReader = $this->factory->createDataPoolReader();
        $logger = $this->factory->getLogger();

        $pageBuilder = new ProductDetailViewRequestHandler(
            $context,
            $dataPoolReader,
            new PageBuilder($dataPoolReader, $snippetKeyGeneratorLocator, $logger),
            $productDetailPageMetaSnippetKeyGenerator
        );
        $page = $pageBuilder->process($request);
        $body = $page->getBody();

        $this->failIfMessagesWhereLogged($logger);

        $expected = '<html><head><title>Page Title</title></head><body><h1>Headline</h1></body></html>';

        $this->assertEquals($expected, $body);
    }
}
