<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Log\LogMessage;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingTemplateSnippetRenderer;

class ProductListingTest extends \PHPUnit_Framework_TestCase
{
    use ProductListingTestTrait;

    private function failIfMessagesWhereLogged(Logger $logger)
    {
        $messages = $logger->getMessages();

        if (count($messages) > 0) {
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

    public function testProductListingSnippetIsWrittenIntoDataPool()
    {
        $this->importCatalog();

        $urlKey = 'adidas-sale';

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[0];

        $productListingSnippetKeyGenerator = $this->factory->createProductListingSnippetKeyGenerator();
        $pageInfoSnippetKey = $productListingSnippetKeyGenerator->getKeyForContext(
            $context,
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );

        $dataPoolReader = $this->factory->createDataPoolReader();
        $metaInfoSnippetJson = $dataPoolReader->getSnippet($pageInfoSnippetKey);
        $metaInfoSnippet = json_decode($metaInfoSnippetJson, true);
        
        $titleKeyGenerator = $this->factory->createProductListingTitleSnippetKeyGenerator();
        $titleKey = $titleKeyGenerator->getKeyForContext($context, [PageMetaInfoSnippetContent::URL_KEY => $urlKey]);
        $titleSnippet = $dataPoolReader->getSnippet($titleKey);
        $this->assertSame('adidas-sale', $titleSnippet);
        
        $expectedCriteriaJson = json_encode(CompositeSearchCriterion::createAnd(
            SearchCriterionGreaterThan::create('stock_qty', '0'),
            SearchCriterionEqual::create('category', 'sale'),
            SearchCriterionEqual::create('brand', 'Adidas')
        ));

        $this->assertEquals(ProductListingTemplateSnippetRenderer::CODE, $metaInfoSnippet['root_snippet_code']);
        $this->assertEquals($expectedCriteriaJson, json_encode($metaInfoSnippet['product_selection_criteria']));
    }

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
    }
}
