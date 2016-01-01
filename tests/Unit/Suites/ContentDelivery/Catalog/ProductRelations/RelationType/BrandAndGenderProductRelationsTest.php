<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\RelationType;

use LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelations;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\SnippetKeyGenerator;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\RelationType\BrandAndGenderProductRelations
 * @uses   \LizardsAndPumpkins\Product\ProductId
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection
 */
class BrandAndGenderProductRelationsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BrandAndGenderProductRelations
     */
    private $brandAndGenderProductRelations;

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
     * @return bool
     */
    private function failIfNotContainsCondition(array $criteria, $field, $condition, $value)
    {
        foreach ($criteria as $criterion) {
            if ($criterion['fieldName'] === $field &&
                $criterion['fieldValue'] === $value &&
                $criterion['operation'] === $condition
            ) {
                return true;
            }
        }
        $this->fail(sprintf('Condition "%s" %s "%s" not set', $field, $condition, $value));
    }

    /**
     * @param string $brand
     * @param string $gender
     * @param string $series
     * @return string
     */
    private function getStubProductDataWithBrandAndGenderAndSeries($brand, $gender, $series)
    {
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
        $this->stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->stubProductJsonSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubContext = $this->getMock(Context::class);

        $this->brandAndGenderProductRelations = new BrandAndGenderProductRelations(
            $this->stubDataPoolReader,
            $this->stubProductJsonSnippetKeyGenerator,
            $this->stubContext
        );
    }

    public function testItImplementsTheProductRelationsInterface()
    {
        $this->assertInstanceOf(ProductRelations::class, $this->brandAndGenderProductRelations);
    }

    /**
     * @param string $missingAttribute
     * @dataProvider missingRequiredAttributeProvider
     */
    public function testItReturnsAnEmptyArrayIfARequiredAttributeIsMissing($missingAttribute)
    {
        /** @var ProductId|\PHPUnit_Framework_MockObject_MockObject $stubProductId */
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $productData = $this->getStubProductDataWithBrandAndGenderAndSeries('Pooma', 'Ladies', 'Example');
        unset($productData['attributes'][$missingAttribute]);
        $this->stubDataPoolReader->method('getSnippet')->willReturn(json_encode($productData));
        
        $result = $this->brandAndGenderProductRelations->getById($stubProductId);
        $this->assertSame([], $result);
    }

    /**
     * @return array[]
     */
    public function missingRequiredAttributeProvider()
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
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $productJson =json_encode($this->getStubProductDataWithBrandAndGenderAndSeries('Pooma', 'Ladies', 'Example'));
        $this->stubDataPoolReader->method('getSnippet')->willReturn($productJson);

        $stubMatchingProductIds = [$this->getMock(ProductId::class, [], [], '', false)];
        $this->stubDataPoolReader->expects($this->once())
            ->method('getProductIdsMatchingCriteria')
            ->willReturnCallback(function (SearchCriteria $criteria) use ($stubMatchingProductIds) {
                $json = json_decode(json_encode($criteria), true);
                $this->failIfNotContainsCondition($json['criteria'], 'brand', 'Equal', 'Pooma');
                $this->failIfNotContainsCondition($json['criteria'], 'gender', 'Equal', 'Ladies');
                $this->failIfNotContainsCondition($json['criteria'], 'series', 'Equal', 'Example');
                return $stubMatchingProductIds;
            });

        $result = $this->brandAndGenderProductRelations->getById($stubProductId);
        $this->assertSame($stubMatchingProductIds, $result);
    }
}
