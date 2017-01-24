<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class FilterNavigationTest extends AbstractIntegrationTest
{
    use ProductListingTemplateIntegrationTestTrait;
    
    private $testUrl = 'http://example.com/sale';

    /**
     * @var MasterFactory
     */
    private $factory;
    
    /**
     * @param string $html
     * @return mixed[]
     */
    private function extractFilterNavigation(string $html) : array
    {
        preg_match('/var filterNavigationJson = (?<json>{[^<]+})/ism', $html, $matches);

        $this->assertNotEmpty($matches, 'Can not find filter navigation JSON in page body');

        $filterNavigation = json_decode($matches['json'], true);

        $this->assertSame(JSON_ERROR_NONE, json_last_error());

        return $filterNavigation;
    }

    protected function setUp()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();
        $fixtures = ['product_listings.xml', 'simple_product_adilette.xml', 'simple_product_armflasher-v1.xml'];
        $this->importCatalogFixture($factory, ...$fixtures);
        $this->importProductListingTemplateFixtureViaApi();
    }

    /**
     * @return mixed[]
     */
    public function testListingPageContainsValidFilterNavigationJson() : array
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString($this->testUrl),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );

        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $productListingRequestHandler = $factory->createProductListingRequestHandler();
        $page = $productListingRequestHandler->process($request);

        return $this->extractFilterNavigation($page->getBody());
    }

    /**
     * @depends testListingPageContainsValidFilterNavigationJson
     * @param mixed[] $initialFilterNavigation
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
            new HttpRequestBody('')
        );

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $productListingRequestHandler = $this->factory->createProductListingRequestHandler();
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
            new HttpRequestBody('')
        );

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $productListingRequestHandler = $this->factory->createProductListingRequestHandler();
        $page = $productListingRequestHandler->process($request);
        $filterNavigation = $this->extractFilterNavigation($page->getBody());

        $this->assertEquals($initialFilterNavigation[$filterCode], $filterNavigation[$filterCode]);
    }
}
