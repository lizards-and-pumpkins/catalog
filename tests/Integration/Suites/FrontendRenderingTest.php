<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Context\VersionedContext;
use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore;
use Brera\DataPool\SearchEngine\InMemorySearchEngine;
use Brera\DataPool\KeyValue\KeyValueStore;
use Brera\Product\ProductDetailPageMetaInfoSnippetContent;
use Brera\Product\ProductDetailViewInContextSnippetRenderer;
use Brera\Product\ProductDetailViewRequestHandler;
use Brera\Product\ProductSnippetKeyGenerator;

class FrontendRenderingTest extends \PHPUnit_Framework_TestCase
{
    private $testProductId = 333;

    public function testPageIsRenderedFromAnUrlWithoutVariablesInSnippets()
    {
        $url = HttpUrl::fromString('http://example.com/product1');
        $context = new VersionedContext(DataVersion::fromVersionString('1.0'));
        $snippetKeyGeneratorLocator = new SnippetKeyGeneratorLocator();
        $urlPathKeyGenerator = new PoCUrlPathKeyGenerator();

        $httpRequest = HttpRequest::fromParameters(
            HttpRequest::HTTP_GET_REQUEST,
            $url,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $keyValueStore = new InMemoryKeyValueStore();
        $searchEngine = new InMemorySearchEngine();

        $this->addPageMetaInfoFixtureToKeyValueStorage(
            $keyValueStore,
            $snippetKeyGeneratorLocator,
            $urlPathKeyGenerator,
            $context
        );

        $dataPoolReader = new DataPoolReader($keyValueStore, $searchEngine);

        $logger = new InMemoryLogger();

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
        KeyValueStore $keyValueStore,
        SnippetKeyGeneratorLocator $snippetKeyGeneratorLocator,
        UrlPathKeyGenerator $urlPathKeyGenerator,
        Context $context
    ) {
        $rootSnippetCode = 'root-snippet';
        $rootSnippetKeyGenerator = new ProductSnippetKeyGenerator(
            ProductDetailViewInContextSnippetRenderer::CODE
        );
        $snippetKeyGeneratorLocator->register($rootSnippetCode, $rootSnippetKeyGenerator);
        $snippetKeyGeneratorLocator->register('head', new GenericSnippetKeyGenerator('head', []));
        $snippetKeyGeneratorLocator->register('body', new GenericSnippetKeyGenerator('body', []));
        $keyValueStore->set(
            $rootSnippetKeyGenerator->getKeyForContext($context, ['product_id' => $this->testProductId]),
            '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>'
        );
        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create(
            $this->testProductId,
            $rootSnippetCode,
            [$rootSnippetCode, 'head', 'body']
        );
        $urlPathKey = ProductDetailViewInContextSnippetRenderer::CODE . '_'
            . $urlPathKeyGenerator->getUrlKeyForPathInContext('/product1', $context);
        $keyValueStore->set($urlPathKey, json_encode($pageMetaInfo->getInfo()));
        $headSnippetKeyGenerator = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode('head');
        $key = $headSnippetKeyGenerator->getKeyForContext($context);
        $keyValueStore->set($key, '<title>Page Title</title>');
        $bodySnippetKeyGenerator = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode('body');
        $key = $bodySnippetKeyGenerator->getKeyForContext($context);
        $keyValueStore->set($key, '<h1>Headline</h1>');
    }
}
