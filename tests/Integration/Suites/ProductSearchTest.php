<?php

namespace Brera;

use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;

class ProductSearchTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->prepareIntegrationTestMasterFactory();
    }
    
    public function testProductSearchResultsMetaSnippetIsWrittenIntoDataPool()
    {
        $this->addPageTemplateWasUpdatedDomainEventToSetupProductListingFixture();

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[1];

        $metaInfoSnippetKeyGenerator = $this->factory->createProductSearchResultMetaSnippetKeyGenerator();
        $metaInfoSnippetKey = $metaInfoSnippetKeyGenerator->getKeyForContext($context, []);

        $dataPoolReader = $this->factory->createDataPoolReader();
        $metaInfoSnippet = $dataPoolReader->getSnippet($metaInfoSnippetKey);

        $expectedMetaInfoContent = [
            'root_snippet_code'  => 'product_listing',
            'page_snippet_codes' => [
                'product_listing',
                'global_notices',
                'breadcrumbsContainer',
                'global_messages',
                'content_block_in_product_listing',
                'before_body_end'
            ]
        ];

        $this->assertSame(json_encode($expectedMetaInfoContent), $metaInfoSnippet);
    }

    private function addPageTemplateWasUpdatedDomainEventToSetupProductListingFixture()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/page_templates/product_listing');
        $httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.brera.page_templates.v1+json']);
        $httpRequestBodyString = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing-root-snippet.xml');
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $website = new SampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }
}
