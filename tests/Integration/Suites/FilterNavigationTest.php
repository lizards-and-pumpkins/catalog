<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;

class FilterNavigationTest extends \PHPUnit_Framework_TestCase
{
    use ProductListingTestTrait;

    private $testUrl = 'http://example.com/sale';

    /**
     * @param string $html
     * @return mixed[]
     */
    private function extractFilterNavigation($html)
    {
        preg_match('/var filterNavigationJson = ({[^<]+})/ism', $html, $matches);

        $this->assertNotEmpty($matches, 'Can not found filter navigation JSON in page body');

        $filterNavigation = json_decode($matches[1], true);

        $this->assertSame(JSON_ERROR_NONE, json_last_error());

        return $filterNavigation;
    }

    protected function setUp()
    {
        $this->importCatalog();
        $this->prepareProductListingFixture();
        $this->registerProductListingSnippetKeyGenerator();
    }

    /**
     * @return mixed[]
     */
    public function testListingPageContainsValidFilterNavigationJson()
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString($this->testUrl),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->factory = $this->createIntegrationTestMasterFactoryForRequest($request);

        $productListingRequestHandler = $this->createProductListingRequestHandler();
        $page = $productListingRequestHandler->process($request);

        return $this->extractFilterNavigation($page->getBody());
    }

    /**
     * @depends testListingPageContainsValidFilterNavigationJson
     * @param array[] $initialFilterNavigation
     */
    public function testFilterNavigationIsChangedIfFilterIsSelected(array $initialFilterNavigation)
    {
        $filterCode = key($initialFilterNavigation);
        $filterValue = $initialFilterNavigation[$filterCode][0]['value'];
        $url = $this->testUrl . '?' . $filterCode . '=' . rawurlencode($filterValue);

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString($url),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->factory = $this->createIntegrationTestMasterFactoryForRequest($request);

        $productListingRequestHandler = $this->createProductListingRequestHandler();
        $page = $productListingRequestHandler->process($request);
        $filterNavigation = $this->extractFilterNavigation($page->getBody());

        $this->assertNotEquals($initialFilterNavigation, $filterNavigation);
    }

    /**
     * @depends testListingPageContainsValidFilterNavigationJson
     * @param array[] $initialFilterNavigation
     */
    public function testSiblingOptionsValuesOfSelectedFilterValueArePresent(array $initialFilterNavigation)
    {
        $filterCode = key($initialFilterNavigation);
        $filterValue = $initialFilterNavigation[$filterCode][0]['value'];
        $url = $this->testUrl . '?' . $filterCode . '=' . rawurlencode($filterValue);

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString($url),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->factory = $this->createIntegrationTestMasterFactoryForRequest($request);

        $productListingRequestHandler = $this->createProductListingRequestHandler();
        $page = $productListingRequestHandler->process($request);
        $filterNavigation = $this->extractFilterNavigation($page->getBody());

        $this->assertEquals($initialFilterNavigation[$filterCode], $filterNavigation[$filterCode]);
    }
}
