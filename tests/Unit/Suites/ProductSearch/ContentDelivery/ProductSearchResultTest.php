<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchResult
 */
class ProductSearchResultTest extends \PHPUnit_Framework_TestCase
{
    private $testTotalNumber = 200;

    private $testProductsData = [['Dummy product A data'], ['Dummy product B data']];

    private $testFacetFieldsArray = ['attribute-name' => ['value' => 'attribute-value', 'count' => 5]];

    /**
     * @var FacetFieldCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFacetFieldCollection;

    /**
     * @var ProductSearchResult
     */
    private $searchResult;

    final protected function setUp()
    {
        $this->stubFacetFieldCollection = $this->createMock(FacetFieldCollection::class);
        $this->stubFacetFieldCollection->method('jsonSerialize')->willReturn($this->testFacetFieldsArray);

        $this->searchResult = new ProductSearchResult(
            $this->testTotalNumber,
            $this->testProductsData,
            $this->stubFacetFieldCollection
        );
    }

    public function testImplementsJsonSerializableInterface()
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->searchResult);
    }

    public function testReturnsArrayRepresentation()
    {
        $expectedArray = [
            'total' => $this->testTotalNumber,
            'data' => $this->testProductsData,
            'facets' => $this->testFacetFieldsArray,
        ];

        $this->assertSame($expectedArray, $this->searchResult->jsonSerialize());
    }

    public function testReturnsTotalNumberOrResults()
    {
        $this->assertSame($this->testTotalNumber, $this->searchResult->getTotalNumberOfResults());
    }

    public function testReturnsProductsDataArray()
    {
        $this->assertSame($this->testProductsData, $this->searchResult->getData());
    }

    public function testReturnsFacetFieldCollection()
    {
        $this->assertSame($this->stubFacetFieldCollection, $this->searchResult->getFacetFieldsCollection());
    }
}
