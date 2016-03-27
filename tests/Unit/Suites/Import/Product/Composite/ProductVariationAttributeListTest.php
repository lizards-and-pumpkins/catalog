<?php


namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Composite\Exception\ProductVariationAttributesEmptyException;
use LizardsAndPumpkins\Import\Product\Composite\Exception\ProductVariationAttributesNotUniqueException;
use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;

/**
 * @covers \LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 */
class ProductVariationAttributeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string[] $attributeCodeStrings
     * @return AttributeCode[]
     */
    private function createAttributeCodeListFromStrings(array $attributeCodeStrings)
    {
        return array_map(function ($code) {
            return AttributeCode::fromString($code);
        }, $attributeCodeStrings);
    }

    public function testItThrowsAnExceptionIfTheVariationListIsEmpty()
    {
        $this->expectException(ProductVariationAttributesEmptyException::class);
        $this->expectExceptionMessage('The product variation attribute list can not be empty');
        new ProductVariationAttributeList();
    }

    public function testItThrowsAnExceptionIfTwoEqualAttributesAreAddedToTheList()
    {
        $attributeCodeOne = AttributeCode::fromString('test');
        $attributeCodeTwo = AttributeCode::fromString('test');

        $this->expectException(ProductVariationAttributesNotUniqueException::class);
        $this->expectExceptionMessage(
            'The product variation attribute list contained the attribute "test" more then once'
        );
        new ProductVariationAttributeList($attributeCodeOne, $attributeCodeTwo);
    }

    public function testItReturnsAProductAttributeVariationList()
    {
        $this->assertInstanceOf(
            ProductVariationAttributeList::class,
            ProductVariationAttributeList::fromArray(['test'])
        );
    }

    public function testItImplementsTheIteratorAggregateInterface()
    {
        $testAttribute = AttributeCode::fromString('test');
        $this->assertInstanceOf(\IteratorAggregate::class, new ProductVariationAttributeList($testAttribute));
    }

    public function testItIteratesOverTheInjectedAttributeCodes()
    {
        $expectedAttributes = [
            AttributeCode::fromString('test_a'),
            AttributeCode::fromString('test_b'),
        ];
        $productVariationsList = new ProductVariationAttributeList(...$expectedAttributes);
        $this->assertSame($expectedAttributes, iterator_to_array($productVariationsList));
    }

    public function testItReturnsTheVariationAttributesArray()
    {
        $expectedAttributes = [
            AttributeCode::fromString('test_a'),
            AttributeCode::fromString('test_b'),
        ];

        $productVariationsList = new ProductVariationAttributeList(...$expectedAttributes);
        $this->assertSame($expectedAttributes, $productVariationsList->getAttributes());
    }

    /**
     * @param string[] $attributeCodeStrings
     * @param int $expectedCount
     * @dataProvider attributeCodeStringCountProvider
     */
    public function testItCountsTheNumberOfAttributesInTheList(array $attributeCodeStrings, $expectedCount)
    {
        $attributeCodes = $this->createAttributeCodeListFromStrings($attributeCodeStrings);
        $this->assertCount($expectedCount, new ProductVariationAttributeList(...$attributeCodes));
    }

    /**
     * @return array[]
     */
    public function attributeCodeStringCountProvider()
    {
        return [
            [['test'], 1],
            [['test_a', 'test_b'], 2],
            [['test_a', 'test_b', 'test_c'], 3],
        ];
    }

    public function testItImplementsJsonSerializable()
    {
        $testAttribute = AttributeCode::fromString('test');
        $this->assertInstanceOf(\JsonSerializable::class, new ProductVariationAttributeList($testAttribute));
    }

    public function testItCanBeJsonSerializedAndRehydrated()
    {
        $attributeCodeStrings = ['test_one', 'test_two'];
        $attributeCodes = $this->createAttributeCodeListFromStrings($attributeCodeStrings);
        $sourceVariationAttributeList = new ProductVariationAttributeList(...$attributeCodes);

        $json = json_encode($sourceVariationAttributeList);
        $rehydratedVariationAttributeList = ProductVariationAttributeList::fromArray(json_decode($json, true));

        $this->assertCount(count($attributeCodes), $rehydratedVariationAttributeList);
        foreach ($rehydratedVariationAttributeList as $rehydratedAttributeCode) {
            $this->assertContains((string) $rehydratedAttributeCode, $attributeCodeStrings);
        }
    }
}
