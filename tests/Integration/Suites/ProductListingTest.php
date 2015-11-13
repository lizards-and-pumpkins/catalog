<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingRequestHandler;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Log\LogMessage;
use LizardsAndPumpkins\Product\ProductListingCriteriaSnippetContent;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetRenderer;
use LizardsAndPumpkins\Utils\XPathParser;

class ProductListingTest extends \PHPUnit_Framework_TestCase
{
    use ProductListingTestTrait;

    private $testUrl = 'http://example.com/sale';

    /**
     * @param string $contentBlockContent
     */
    private function addContentBlockToDataPool($contentBlockContent)
    {
        $testUrlKey = preg_replace('/.*\//', '', $this->testUrl);
        $httpUrl = HttpUrl::fromString('http://example.com/api/content_blocks/in_product_listing_' . $testUrlKey);
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.content_blocks.v1+json'
        ]);
        $httpRequestBodyString = json_encode([
            'content' => $contentBlockContent,
            'context' => ['website' => 'ru', 'locale' => 'de_DE']
        ]);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $website = new InjectableSampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    /**
     * @return mixed[]
     */
    private function getStubMetaInfo()
    {
        $searchCriterion1 = SearchCriterionEqual::create('category', 'sale');
        $searchCriterion2 = SearchCriterionEqual::create('brand', 'Adidas');
        $searchCriteria = CompositeSearchCriterion::createAnd($searchCriterion1, $searchCriterion2);

        $pageSnippetCodes = [
            'global_notices',
            'breadcrumbsContainer',
            'global_messages',
            'content_block_in_product_listing',
            'before_body_end'
        ];

        $metaSnippetContent = ProductListingCriteriaSnippetContent::create(
            $searchCriteria,
            ProductListingPageSnippetRenderer::CODE,
            $pageSnippetCodes
        );

        return $metaSnippetContent->getInfo();
    }

    private function registerContentBlockInProductListingSnippetKeyGenerator()
    {
        $this->factory->getSnippetKeyGeneratorLocator()->register(
            'content_block_in_product_listing',
            $this->factory->createContentBlockInProductListingSnippetKeyGenerator()
        );
    }

    private function failIfMessagesWhereLogged(Logger $logger)
    {
        $messages = $logger->getMessages();

        if (!empty($messages)) {
            $failMessages = array_map(function (LogMessage $logMessage) {
                $messageContext = $logMessage->getContext();
                if (isset($messageContext['exception'])) {
                    /** @var \Exception $exception */
                    $exception = $messageContext['exception'];
                    return (string)$logMessage . ' ' . $exception->getFile() . ':' . $exception->getLine();
                }
                return (string)$logMessage;
            }, $messages);
            $failMessageString = implode(PHP_EOL, $failMessages);

            $this->fail($failMessageString);
        }
    }

    /**
     * @param string $html
     * @return mixed[]
     */
    private function extractProductListingJsonFromPageHtml($html)
    {
        $productListingJsonString = preg_replace('/.*var productListingJson = |,\n\s*productPrices.*/ism', '', $html);
        return json_decode($productListingJsonString, true);
    }

    /**
     * @param array[] $productListingJson
     */
    private function assertJsonProductsAreSortedByPriceAscendant(array $productListingJson)
    {
        array_reduce($productListingJson, function($previousPrice, $productJson) {
            $productPrice = $productJson['attributes']['price'];
            if ($previousPrice > $productPrice) {
                $this->fail('Failed asserting that product JSON is sorted by price.');
            }
        }, 0);

        $this->assertTrue(true);
    }

    public function testProductListingCriteriaSnippetIsWrittenIntoDataPool()
    {
        $this->importCatalog();

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');
        $urlKeyNode = (new XPathParser($xml))->getXmlNodesArrayByXPath('//catalog/listings/listing[1]/@url_key');
        $urlKey = $urlKeyNode[0]['value'];

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[0];

        $productListingCriteriaSnippetKeyGenerator = $this->factory->createProductListingCriteriaSnippetKeyGenerator();
        $snippetKey = $productListingCriteriaSnippetKeyGenerator->getKeyForContext(
            $context,
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );

        $dataPoolReader = $this->factory->createDataPoolReader();
        $metaInfoSnippetJson = $dataPoolReader->getSnippet($snippetKey);
        $metaInfoSnippet = json_decode($metaInfoSnippetJson, true);

        $expectedCriteria = CompositeSearchCriterion::createAnd(
            SearchCriterionEqual::create('category', 'sale'),
            SearchCriterionEqual::create('brand', 'Adidas')
        );

        $this->assertEquals(ProductListingPageSnippetRenderer::CODE, $metaInfoSnippet['root_snippet_code']);
        $this->assertEquals(json_encode($expectedCriteria), json_encode($metaInfoSnippet['product_selection_criteria']));
    }

    public function testProductListingPageHtmlIsReturned()
    {
        $this->importCatalog();
        $this->createProductListingFixture();
        $this->registerProductListingSnippetKeyGenerator();

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString($this->testUrl),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $productListingRequestHandler = $this->getProductListingRequestHandler();
        $page = $productListingRequestHandler->process($request);
        $body = $page->getBody();

        /* TODO: read from XML */
        $expectedProductName = 'Gel-Noosa';
        $unExpectedProductName = 'LED Armflasher';

        $this->assertContains($expectedProductName, $body);
        $this->assertNotContains($unExpectedProductName, $body);
    }
}
