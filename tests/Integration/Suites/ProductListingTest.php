<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Log\LogMessage;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetRenderer;

class ProductListingTest extends \PHPUnit_Framework_TestCase
{
    use ProductListingTestTrait;

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

    public function testProductListingCriteriaSnippetIsWrittenIntoDataPool()
    {
        $this->importCatalog();

        $urlKey = 'adidas-sale';

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
            CompositeSearchCriterion::createOr(
                SearchCriterionGreaterThan::create('stock_qty', '0'),
                SearchCriterionEqual::create('backorders', 'true')
            ),
            SearchCriterionEqual::create('category', 'sale'),
            SearchCriterionEqual::create('brand', 'Adidas')
        );

        $this->assertEquals(ProductListingPageSnippetRenderer::CODE, $metaInfoSnippet['root_snippet_code']);
        $this->assertEquals(json_encode($expectedCriteria), json_encode($metaInfoSnippet['product_selection_criteria']));
    }

    /**
     * @return HttpResponse
     */
    public function testProductListingPageHtmlIsReturned()
    {
        $this->importCatalog();
        $this->prepareProductListingFixture();
        $this->registerProductListingSnippetKeyGenerator();

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com/sale'),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->factory = $this->createIntegrationTestMasterFactoryForRequest($request);

        $productListingRequestHandler = $this->createProductListingRequestHandler();
        $page = $productListingRequestHandler->process($request);
        $body = $page->getBody();

        $expectedProductName = 'Gel-Noosa';
        $unExpectedProductName = 'LED Armflasher';

        $this->assertContains($expectedProductName, $body);
        $this->assertNotContains($unExpectedProductName, $body);

        return $page;
    }

    /**
     * @depends testProductListingPageHtmlIsReturned
     * @param HttpResponse $page
     */
    public function testProductListingPageDoesNotContainOutOfStockProducts(HttpResponse $page)
    {
        $expectedProductName = 'Adilette';
        $unExpectedProductName = 'Adilette Out Of Stock';

        $body = $page->getBody();

        $this->assertContains($expectedProductName, $body);
        $this->assertNotContains($unExpectedProductName, $body);
    }
}
