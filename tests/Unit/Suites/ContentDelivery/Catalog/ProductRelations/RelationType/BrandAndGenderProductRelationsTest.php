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
     * @return string
     */
    private function getProductJsonWithBrandAndGender($brand, $gender)
    {
        $product = [
            'product_id' => 'test',
            'attributes' => [
                'brand' => $brand,
                'gender' => $gender,
            ]
        ];
        return json_encode($product);
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

    public function testItQueriesTheDataPoolForProductIdsMatchingTheBrandAndGender()
    {
        /** @var ProductId|\PHPUnit_Framework_MockObject_MockObject $stubProductId */
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $productJson = $this->getProductJsonWithBrandAndGender('Pooma', 'Ladies');
        $this->stubDataPoolReader->method('getSnippet')->willReturn($productJson);

        $stubMatchingProductIds = [$this->getMock(ProductId::class, [], [], '', false)];
        $this->stubDataPoolReader->expects($this->once())
            ->method('getProductIdsMatchingCriteria')
            ->willReturnCallback(function (SearchCriteria $criteria) use ($stubMatchingProductIds) {
                $json = json_decode(json_encode($criteria), true);
                $this->failIfNotContainsCondition($json['criteria'], 'brand', 'Equal', 'Pooma');
                $this->failIfNotContainsCondition($json['criteria'], 'gender', 'Equal', 'Ladies');
                return $stubMatchingProductIds;
            });

        $result = $this->brandAndGenderProductRelations->getById($stubProductId);
        $this->assertSame($stubMatchingProductIds, $result);
    }

    public function testTheReturnedArrayIsNumericallyIndexed()
    {
        /** @var ProductId|\PHPUnit_Framework_MockObject_MockObject $stubProductId */
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $productJson = $this->getProductJsonWithBrandAndGender('Pooma', 'Ladies');
        $this->stubDataPoolReader->method('getSnippet')->willReturn($productJson);
        $stubMatchingProductIds = ['some-non-numeric-key' => $this->getMock(ProductId::class, [], [], '', false)];
        $this->stubDataPoolReader
            ->method('getProductIdsMatchingCriteria')
            ->willReturn($stubMatchingProductIds);

        $result = $this->brandAndGenderProductRelations->getById($stubProductId);
        $this->assertSame([0], array_keys($result));
    }
}
