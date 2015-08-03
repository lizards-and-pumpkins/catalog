<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Context\VersionedContext;
use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;
use Brera\Product\ProductDetailPageMetaInfoSnippetContent;
use Brera\Product\ProductDetailViewInContextSnippetRenderer;
use Brera\Product\ProductDetailViewRequestHandler;

class FrontendRenderingTest extends AbstractIntegrationTest
{
    private $testProductId = 333;

    /**
     * @var SampleMasterFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->prepareIntegrationTestMasterFactory();
    }

    public function testPageIsRenderedFromAnUrlWithoutVariablesInSnippets()
    {
        $url = HttpUrl::fromString('http://example.com/product1');
        $urlKey = $url->getPathRelativeToWebFront();
        $context = new VersionedContext(DataVersion::fromVersionString('1'));
        $snippetKeyGeneratorLocator = $this->factory->getSnippetKeyGeneratorLocator();
        $productDetailPageMetaSnippetKeyGenerator = $this->factory->createProductDetailPageMetaSnippetKeyGenerator();
        $productDetailPageMetaSnippetKey = $productDetailPageMetaSnippetKeyGenerator->getKeyForContext(
            $context,
            ['url_key' => $urlKey]
        );

        $httpRequest = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $url,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->addPageMetaInfoFixtureToKeyValueStorage(
            $snippetKeyGeneratorLocator,
            $productDetailPageMetaSnippetKey,
            $context
        );

        $dataPoolReader = $this->factory->createDataPoolReader();
        $logger = $this->factory->getLogger();

        $pageBuilder = new ProductDetailViewRequestHandler(
            $productDetailPageMetaSnippetKey,
            $context,
            $dataPoolReader,
            new PageBuilder($dataPoolReader, $snippetKeyGeneratorLocator, $logger)
        );
        $page = $pageBuilder->process($httpRequest);
        $body = $page->getBody();

        $this->failIfMessagesWhereLogged($logger);

        $expected = '<html><head><title>Page Title</title></head><body><h1>Headline</h1></body></html>';

        $this->assertEquals($expected, $body);
    }

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
            ['product_id']
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
            $rootSnippetKeyGenerator->getKeyForContext($context, ['product_id' => $this->testProductId]),
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
        $key = $headSnippetKeyGenerator->getKeyForContext($context, ['product_id' => $this->testProductId]);
        $headSnippet = Snippet::create($key, '<title>Page Title</title>');
        $dataPoolWriter->writeSnippet($headSnippet);

        $bodySnippetKeyGenerator = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode('body');
        $key = $bodySnippetKeyGenerator->getKeyForContext($context, ['product_id' => $this->testProductId]);
        $bodySnippet = Snippet::create($key, '<h1>Headline</h1>');
        $dataPoolWriter->writeSnippet($bodySnippet);
    }
}
