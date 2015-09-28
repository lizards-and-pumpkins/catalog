<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\ConflictingContextDataForProductAttributeListException;
use LizardsAndPumpkins\Product\Exception\ProductAttributeNotFoundException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 */
class ProductAttributeListTest extends \PHPUnit_Framework_TestCase
{
    public function testCountableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\Countable::class, new ProductAttributeList());
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\JsonSerializable::class, new ProductAttributeList());
    }

    public function testItReturnsTheNumberOfAttributes()
    {
        $attributeArray = [
            'code' => 'foo',
            'contextData' => [],
            'value' => 'bar'
        ];
        $this->assertCount(1, ProductAttributeList::fromArray([$attributeArray]));
        $this->assertCount(2, ProductAttributeList::fromArray([$attributeArray, $attributeArray]));
    }

    public function testAnAttributeCanBeRetrievedUsingAString()
    {
        $attribute1 = [
            'code' => 'foo',
            'contextData' => [],
            'value' => 'bar1'
        ];
        $attribute2 = [
            'code' => 'foo',
            'contextData' => [],
            'value' => 'bar2'
        ];

        $attributeList = new ProductAttributeList(
            ProductAttribute::fromArray($attribute1),
            ProductAttribute::fromArray($attribute2)
        );

        $attributesWithCode = $attributeList->getAttributesWithCode('foo');
        $this->assertEquals('bar1', $attributesWithCode[0]->getValue());
        $this->assertEquals('bar2', $attributesWithCode[1]->getValue());
    }

    public function testAnAttributeCanBeRetrievedUsingAnAttributeCodeInstance()
    {
        $attributeArray = [
            'code' => 'foo',
            'contextData' => [],
            'value' => 'bar'
        ];

        $attributeList = new ProductAttributeList(ProductAttribute::fromArray($attributeArray));

        $attributesWithCode = $attributeList->getAttributesWithCode(AttributeCode::fromString('foo'));
        $this->assertEquals('bar', $attributesWithCode[0]->getValue());
    }

    public function testExceptionIsThrownIfNoAttributeWithGivenCodeIsSet()
    {
        $this->setExpectedException(ProductAttributeNotFoundException::class);
        (new ProductAttributeList())->getAttributesWithCode('foo');
    }

    public function testAttributeListIsCreatedFromAttributesArray()
    {
        $attributeArray = [
            [
                'code' => 'foo',
                'contextData' => [],
                'value' => 'bar'
            ]
        ];

        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $attributesWithCode = $attributeList->getAttributesWithCode('foo');
        $attributeWithCode = $attributesWithCode[0];

        $this->assertEquals('bar', $attributeWithCode->getValue());
    }

    public function testItMayContainMultipleProductAttributesWithTheSameCode()
    {
        $attributeArray = [
            ['code' => 'foo', 'contextData' => [], 'value' => 'bar'],
            ['code' => 'foo', 'contextData' => [], 'value' => 'baz'],
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
    public function testItReturnsTheCodesOfAttributesInTheList($numAttributesToAdd, $expected)
    {
        $attributes = [];
        for ($i = 0; $i < $numAttributesToAdd; $i++) {
            $attributes[] = ProductAttribute::fromArray([
                'code' => 'attr_' . ($i + 1),
                'contextData' => [],
                'value' => 'value'
            ]);
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
    public function numberOfAttributesToAddProvider()
    {
        return [
            [0, []],
            [1, ['attr_1']],
            [2, ['attr_1', 'attr_2']],
        ];
    }

    public function testHasAttributeReturnsFalseForAttributesNotInTheList()
    {
        $this->assertFalse((new ProductAttributeList())->hasAttribute('foo'));
    }

    public function testHasAttributeReturnsTrueForAttributesInTheList()
    {
        $attributeArray = [['code' => 'foo', 'contextData' => [], 'value' => 'bar']];
        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $this->assertTrue($attributeList->hasAttribute('foo'));
    }

    public function testItCanBeSerializedAndRehydrated()
    {
        $attributesArray = [
            [
                'code' => 'foo',
                'contextData' => [],
                'value' => 'bar'
            ],
            [
                'code' => 'bar',
                'contextData' => [],
                'value' => 'buz'
            ]
        ];
        $sourceAttributeList = ProductAttributeList::fromArray($attributesArray);

        $json = json_encode($sourceAttributeList);

        $rehydratedAttributeList = ProductAttributeList::fromArray(json_decode($json, true));
        $this->assertEquals($sourceAttributeList->getAttributeCodes(), $rehydratedAttributeList->getAttributeCodes());
    }

    public function testItThrowsAnExceptionIfContextWithIncompatibleContextDataAreInjected()
    {
        $expectedMessage = 'Conflicting context "locale" data set values found ' .
            'for attributes to be included in one attribute list: "xx_XX" != "yy_YY"';
        $this->setExpectedException(ConflictingContextDataForProductAttributeListException::class, $expectedMessage);
        $attributesArray = [
            [
                'code' => 'test1',
                'contextData' => ['website' => 'a'],
                'value' => 'test'
            ],
            [
                'code' => 'test1',
                'contextData' => ['website' => 'a', 'locale' => 'xx_XX'],
                'value' => 'test'
            ],
            [
                'code' => 'test2',
                'contextData' => ['website' => 'a', 'locale' => 'yy_YY'],
                'value' => 'test'
            ]
        ];
        ProductAttributeList::fromArray($attributesArray);
    }
}
