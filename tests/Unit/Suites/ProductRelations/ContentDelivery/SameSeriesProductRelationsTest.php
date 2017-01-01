<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\ProductRelations\ProductRelations;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;

/**
 * @covers \LizardsAndPumpkins\ProductRelations\ContentDelivery\SameSeriesProductRelations
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionNotEqual
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection
 */
class SameSeriesProductRelationsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SameSeriesProductRelations
     */
    private $sameSeriesProductRelations;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDataPoolReader;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductJsonSnippetKeyGenerator;

    /**
     * @param array[] $criteria
     * @param string $field
     * @param string $condition
     * @param string $value
     */
    private function failIfNotContainsCondition(array $criteria, string $field, string $condition, string $value)
    {
        $expectedCriterion = ['fieldName' => $field, 'fieldValue' => $value, 'operation' => $condition];

        if (!in_array($expectedCriterion, $criteria)) {
            $this->fail(sprintf('Condition "%s" %s "%s" not set', $field, $condition, $value));
        }
    }

    /**
     * @param array[] $criteria
     */
    private function failIfStockAvailabilityConditionIsNotFound(array $criteria)
    {
        $expectedCriterion = [
            'condition' => CompositeSearchCriterion::OR_CONDITION,
            'criteria' => [
                ['fieldName' => 'stock_qty', 'fieldValue' => 0, 'operation' => 'GreaterThan'],
                ['fieldName' => 'backorders', 'fieldValue' => 'true', 'operation' => 'Equal']
            ]
        ];

        if (!in_array($expectedCriterion, $criteria)) {
            $this->fail('Stock availability condition is not found in criteria.');
        }
    }

    /**
     * @param string $brand
     * @param string $gender
     * @param string $series
     * @return mixed[]
     */
    private function getStubProductDataWithBrandAndGenderAndSeries(
        string $brand,
        string $gender,
        string $series
    ) : array {
        return [
            'product_id' => 'test',
            'attributes' => [
                'brand' => $brand,
                'gender' => $gender,
                'series' => $series,
            ]
        ];
    }

    protected function setUp()
    {
        $this->stubDataPoolReader = $this->createMock(DataPoolReader::class);
        $this->stubProductJsonSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $this->stubContext = $this->createMock(Context::class);

        $this->sameSeriesProductRelations = new SameSeriesProductRelations(
            $this->stubDataPoolReader,
            $this->stubProductJsonSnippetKeyGenerator,
            $this->stubContext
        );
    }

    public function testItImplementsTheProductRelationsInterface()
    {
        $this->assertInstanceOf(ProductRelations::class, $this->sameSeriesProductRelations);
    }

    /**
     * @dataProvider missingRequiredAttributeProvider
     */
    public function testItReturnsAnEmptyArrayIfARequiredAttributeIsMissing(string $missingAttribute)
    {
        /** @var ProductId|\PHPUnit_Framework_MockObject_MockObject $stubProductId */
        $stubProductId = $this->createMock(ProductId::class);

        $productData = $this->getStubProductDataWithBrandAndGenderAndSeries('Pooma', 'Ladies', 'Example');
        unset($productData['attributes'][$missingAttribute]);
        $this->stubDataPoolReader->method('getSnippet')->willReturn(json_encode($productData));
        
        $result = $this->sameSeriesProductRelations->getById($this->stubContext, $stubProductId);
        $this->assertSame([], $result);
    }

    /**
     * @return array[]
     */
    public function missingRequiredAttributeProvider() : array
    {
        return [
            ['gender'],
            ['brand'],
            ['series'],
        ];
    }

    public function testItQueriesTheDataPoolForProductIdsMatchingTheBrandAndSeriesAndGender()
    {
        /** @var ProductId|\PHPUnit_Framework_MockObject_MockObject $stubProductId */
        $stubProductId = $this->createMock(ProductId::class);

        $productJson = json_encode($this->getStubProductDataWithBrandAndGenderAndSeries('Pooma', 'Ladies', 'Example'));
        $this->stubDataPoolReader->method('getSnippet')->willReturn($productJson);

        $stubMatchingProductIds = [$this->createMock(ProductId::class)];
        $this->stubDataPoolReader->expects($this->once())
            ->method('getProductIdsMatchingCriteria')
            ->willReturnCallback(function (SearchCriteria $criteria) use ($stubMatchingProductIds) {
                $json = json_decode(json_encode($criteria), true);
                $this->failIfNotContainsCondition($json['criteria'], 'brand', 'Equal', 'Pooma');
                $this->failIfNotContainsCondition($json['criteria'], 'gender', 'Equal', 'Ladies');
                $this->failIfNotContainsCondition($json['criteria'], 'series', 'Equal', 'Example');
                $this->failIfStockAvailabilityConditionIsNotFound($json['criteria']);
                return $stubMatchingProductIds;
            });

        $result = $this->sameSeriesProductRelations->getById($this->stubContext, $stubProductId);
        $this->assertSame($stubMatchingProductIds, $result);
    }
}
