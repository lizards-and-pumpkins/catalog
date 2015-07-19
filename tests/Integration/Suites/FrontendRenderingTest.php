<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Context\VersionedContext;
use Brera\DataPool\DataPoolWriter;
use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;
use Brera\Product\ProductDetailPageMetaInfoSnippetContent;
use Brera\Product\ProductDetailViewInContextSnippetRenderer;
use Brera\Product\ProductDetailViewRequestHandler;
use Brera\Product\ProductSnippetKeyGenerator;

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
        $context = new VersionedContext(DataVersion::fromVersionString('1'));
        $snippetKeyGeneratorLocator = $this->factory->getSnippetKeyGeneratorLocator();
        $urlPathKeyGenerator = new SampleUrlPathKeyGenerator();

        $httpRequest = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $url,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $dataPoolWriter = $this->factory->createDataPoolWriter();

        $this->addPageMetaInfoFixtureToKeyValueStorage(
            $dataPoolWriter,
            $snippetKeyGeneratorLocator,
            $urlPathKeyGenerator,
            $context
        );

        $dataPoolReader = $this->factory->createDataPoolReader();
        $logger = $this->factory->getLogger();

        $pageBuilder = new ProductDetailViewRequestHandler(
            $urlPathKeyGenerator->getUrlKeyForUrlInContext($url, $context),
            $context,
            $dataPoolReader,
            new PageBuilder($dataPoolReader, $snippetKeyGeneratorLocator, $logger)
        );
        $page = $pageBuilder->process($httpRequest);

        $body = $page->getBody();
        $expected = '<html><head><title>Page Title</title></head><body><h1>Headline</h1></body></html>';

        $this->assertEquals($expected, $body);
    }

    private function addPageMetaInfoFixtureToKeyValueStorage(
        DataPoolWriter $dataPoolWriter,
        SnippetKeyGeneratorLocator $snippetKeyGeneratorLocator,
        UrlPathKeyGenerator $urlPathKeyGenerator,
        Context $context
    ) {
        $rootSnippetCode = 'root-snippet';
        $rootSnippetKeyGenerator = new ProductSnippetKeyGenerator(
            ProductDetailViewInContextSnippetRenderer::CODE,
            $this->factory->getRequiredContexts()
        );
        $snippetKeyGeneratorLocator->register($rootSnippetCode, $rootSnippetKeyGenerator);
        $snippetKeyGeneratorLocator->register('head', new GenericSnippetKeyGenerator('head', []));
        $snippetKeyGeneratorLocator->register('body', new GenericSnippetKeyGenerator('body', []));

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
        $urlPathKey = ProductDetailViewInContextSnippetRenderer::CODE . '_'
            . $urlPathKeyGenerator->getUrlKeyForPathInContext('/product1', $context);
        $metaInfoSnippet = Snippet::create($urlPathKey, json_encode($pageMetaInfo->getInfo()));
        $dataPoolWriter->writeSnippet($metaInfoSnippet);

        $headSnippetKeyGenerator = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode('head');
        $key = $headSnippetKeyGenerator->getKeyForContext($context);
        $headSnippet = Snippet::create($key, '<title>Page Title</title>');
        $dataPoolWriter->writeSnippet($headSnippet);

        $bodySnippetKeyGenerator = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode('body');
        $key = $bodySnippetKeyGenerator->getKeyForContext($context);
        $bodySnippet = Snippet::create($key, '<h1>Headline</h1>');
        $dataPoolWriter->writeSnippet($bodySnippet);
    }
}
