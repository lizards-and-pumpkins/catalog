<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Log\LogMessage;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetRenderer;
use LizardsAndPumpkins\Utils\XPathParser;

class ProductListingTest extends \PHPUnit_Framework_TestCase
{
    use ProductListingTestTrait;

    private $testUrl = 'http://example.com/sale';

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
        $this->prepareProductListingFixture();
        $this->registerProductListingSnippetKeyGenerator();

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString($this->testUrl),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->factory = $this->createIntegrationTestMasterFactoryForRequest($request);

        $productListingRequestHandler = $this->createProductListingRequestHandler();
        $page = $productListingRequestHandler->process($request);
        $body = $page->getBody();

        /* TODO: read from XML */
        $expectedProductName = 'Gel-Noosa';
        $unExpectedProductName = 'LED Armflasher';

        $this->assertContains($expectedProductName, $body);
        $this->assertNotContains($unExpectedProductName, $body);
    }
}
