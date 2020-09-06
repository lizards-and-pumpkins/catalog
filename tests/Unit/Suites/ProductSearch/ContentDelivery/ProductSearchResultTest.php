<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchResult
 */
class ProductSearchResultTest extends TestCase
{
    private $testTotalNumber = 200;

    private $testProductsData = [['Dummy product A data'], ['Dummy product B data']];

    private $testFacetFieldsArray = ['attribute-name' => ['value' => 'attribute-value', 'count' => 5]];

    /**
     * @var FacetFieldCollection|MockObject
     */
    private $stubFacetFieldCollection;

    /**
     * @var ProductSearchResult
     */
    private $searchResult;

    final protected function setUp(): void
    {
        $this->stubFacetFieldCollection = $this->createMock(FacetFieldCollection::class);
        $this->stubFacetFieldCollection->method('jsonSerialize')->willReturn($this->testFacetFieldsArray);

        $this->searchResult = new ProductSearchResult(
            $this->testTotalNumber,
            $this->testProductsData,
            $this->stubFacetFieldCollection
        );
    }

    public function testImplementsJsonSerializableInterface(): void
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->searchResult);
    }

    public function testReturnsArrayRepresentation(): void
    {
        $expectedArray = [
            'total' => $this->testTotalNumber,
            'data' => $this->testProductsData,
            'facets' => $this->testFacetFieldsArray,
        ];

        $this->assertSame($expectedArray, $this->searchResult->jsonSerialize());
    }

    public function testReturnsTotalNumberOrResults(): void
    {
        $this->assertSame($this->testTotalNumber, $this->searchResult->getTotalNumberOfResults());
    }

    public function testReturnsProductsDataArray(): void
    {
        $this->assertSame($this->testProductsData, $this->searchResult->getData());
    }

    public function testReturnsFacetFieldCollection(): void
    {
        $this->assertSame($this->stubFacetFieldCollection, $this->searchResult->getFacetFieldCollection());
    }
}
