<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Exception\ConflictingContextDataForProductAttributeListException;
use LizardsAndPumpkins\Import\Product\Exception\ProductAttributeNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 */
class ProductAttributeListTest extends TestCase
{
    public function testCountableInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(\Countable::class, new ProductAttributeList());
    }

    public function testJsonSerializableInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(\JsonSerializable::class, new ProductAttributeList());
    }

    public function testItReturnsTheNumberOfAttributes(): void
    {
        $attributeArray = [
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT => [],
            ProductAttribute::VALUE => 'bar'
        ];
        $this->assertCount(1, ProductAttributeList::fromArray([$attributeArray]));
        $this->assertCount(2, ProductAttributeList::fromArray([$attributeArray, $attributeArray]));
    }

    public function testAnAttributeCanBeRetrievedUsingAString(): void
    {
        $attribute1 = [
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT => [],
            ProductAttribute::VALUE => 'bar1'
        ];
        $attribute2 = [
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT => [],
            ProductAttribute::VALUE => 'bar2'
        ];

        $attributeList = new ProductAttributeList(
            ProductAttribute::fromArray($attribute1),
            ProductAttribute::fromArray($attribute2)
        );

        $attributesWithCode = $attributeList->getAttributesWithCode('foo');
        $this->assertEquals('bar1', $attributesWithCode[0]->getValue());
        $this->assertEquals('bar2', $attributesWithCode[1]->getValue());
    }

    public function testAnAttributeCanBeRetrievedUsingAnAttributeCodeInstance(): void
    {
        $attributeArray = [
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT => [],
            ProductAttribute::VALUE => 'bar'
        ];

        $attributeList = new ProductAttributeList(ProductAttribute::fromArray($attributeArray));

        $attributesWithCode = $attributeList->getAttributesWithCode(AttributeCode::fromString('foo'));
        $this->assertEquals('bar', $attributesWithCode[0]->getValue());
    }

    public function testExceptionIsThrownIfNoAttributeWithGivenCodeIsSet(): void
    {
        $this->expectException(ProductAttributeNotFoundException::class);
        (new ProductAttributeList())->getAttributesWithCode('foo');
    }

    public function testAttributeListIsCreatedFromAttributesArray(): void
    {
        $attributeArray = [
            [
                ProductAttribute::CODE => 'foo',
                ProductAttribute::CONTEXT => [],
                ProductAttribute::VALUE => 'bar'
            ]
        ];

        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $attributesWithCode = $attributeList->getAttributesWithCode('foo');
        $attributeWithCode = $attributesWithCode[0];

        $this->assertEquals('bar', $attributeWithCode->getValue());
    }

    public function testItMayContainMultipleProductAttributesWithTheSameCode(): void
    {
        $attributeArray = [
            [ProductAttribute::CODE => 'foo', ProductAttribute::CONTEXT => [], ProductAttribute::VALUE => 'bar'],
            [ProductAttribute::CODE => 'foo', ProductAttribute::CONTEXT => [], ProductAttribute::VALUE => 'baz'],
        ];

        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $result = $attributeList->getAttributesWithCode('foo');

        $this->assertCount(count($attributeArray), $result);
        $this->assertContainsOnly(ProductAttribute::class, $result);
    }

    /**
     * @param int $numAttributesToAdd
     * @param string[] $expected
     * @dataProvider numberOfAttributesToAddProvider
     */
    public function testItReturnsTheCodesOfAttributesInTheList(int $numAttributesToAdd, array $expected): void
    {
        $attributes = [];
        for ($i = 0; $i < $numAttributesToAdd; $i++) {
            $code = 'attr_' . ($i + 1);
            $value = 'some dummy value';
            $contextData = [];
            $attributes[] = new ProductAttribute($code, $value, $contextData);
        }
        
        $attributeCodes = (new ProductAttributeList(...$attributes))->getAttributeCodes();
        $this->assertContainsOnly(AttributeCode::class, $attributeCodes);
        $this->assertSame(count($expected), count($attributeCodes));
        foreach ($expected as $idx => $expectedAttributeCodeString) {
            $expectedAttributeCode = AttributeCode::fromString($expectedAttributeCodeString);
            $this->assertTrue($expectedAttributeCode->isEqualTo($attributeCodes[$idx]));
        }
    }

    /**
     * @return array[]
     */
    public function numberOfAttributesToAddProvider() : array
    {
        return [
            [0, []],
            [1, ['attr_1']],
            [2, ['attr_1', 'attr_2']],
        ];
    }

    public function testHasAttributeReturnsFalseForAttributesNotInTheList(): void
    {
        $this->assertFalse((new ProductAttributeList())->hasAttribute('foo'));
    }

    public function testHasAttributeReturnsTrueForAttributesInTheList(): void
    {
        $attributeArray = [[
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT => [],
            ProductAttribute::VALUE => 'bar'
        ]];
        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $this->assertTrue($attributeList->hasAttribute('foo'));
    }

    public function testItCanBeSerializedAndRehydrated(): void
    {
        $attributesArray = [
            [
                ProductAttribute::CODE => 'foo',
                ProductAttribute::CONTEXT => [],
                ProductAttribute::VALUE => 'bar'
            ],
            [
                ProductAttribute::CODE => 'bar',
                ProductAttribute::CONTEXT => [],
                ProductAttribute::VALUE => 'buz'
            ]
        ];
        $sourceAttributeList = ProductAttributeList::fromArray($attributesArray);

        $json = json_encode($sourceAttributeList);

        $rehydratedAttributeList = ProductAttributeList::fromArray(json_decode($json, true));
        $this->assertEquals($sourceAttributeList->getAttributeCodes(), $rehydratedAttributeList->getAttributeCodes());
    }

    public function testItThrowsAnExceptionIfContextWithIncompatibleContextDataAreInjected(): void
    {
        $this->expectException(ConflictingContextDataForProductAttributeListException::class);
        $expectedMessage = 'Conflicting context "locale" data set values found ' .
            'for attributes to be included in one attribute list: "xx_XX" != "yy_YY"';
        $this->expectExceptionMessage($expectedMessage);
        $attributesArray = [
            [
                ProductAttribute::CODE => 'test1',
                ProductAttribute::CONTEXT => ['website' => 'a'],
                ProductAttribute::VALUE => 'test'
            ],
            [
                ProductAttribute::CODE => 'test1',
                ProductAttribute::CONTEXT => ['website' => 'a', 'locale' => 'xx_XX'],
                ProductAttribute::VALUE => 'test'
            ],
            [
                ProductAttribute::CODE => 'test2',
                ProductAttribute::CONTEXT => ['website' => 'a', 'locale' => 'yy_YY'],
                ProductAttribute::VALUE => 'test'
            ]
        ];
        ProductAttributeList::fromArray($attributesArray);
    }

    public function testItReturnsAllAttributes(): void
    {
        $productAttributes = [
            new ProductAttribute('number_one', 1, []),
            new ProductAttribute('number_two', 2, []),
            new ProductAttribute('number_three', 3, []),
        ];
        $productAttributeList = new ProductAttributeList(...$productAttributes);
        
        $this->assertSame($productAttributes, $productAttributeList->getAllAttributes());
    }
}
