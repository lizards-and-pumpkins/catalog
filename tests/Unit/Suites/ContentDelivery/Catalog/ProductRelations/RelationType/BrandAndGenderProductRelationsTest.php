<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\RelationType;

use LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelations;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextVersion;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductImage\ProductImageList;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\Product\Tax\ProductTaxClass;
use LizardsAndPumpkins\SnippetKeyGenerator;

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
        $contextData = [];
        $brandAttribute = new ProductAttribute('brand', $brand, $contextData);
        $genderAttribute = new ProductAttribute('gender', $gender, $contextData);
        $this->stubContext->method('jsonSerialize')->willReturn([ContextVersion::CODE => '-1']);
        $product = new SimpleProduct(
            ProductId::fromString('test'),
            ProductTaxClass::fromString('test'),
            new ProductAttributeList($brandAttribute, $genderAttribute),
            new ProductImageList(),
            $this->stubContext
        );
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
}
