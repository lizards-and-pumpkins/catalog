<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Exception\ProductAttributeContextPartsMismatchException;
use LizardsAndPumpkins\Product\Exception\ProductAttributeNotFoundException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 */
class ProductAttributeListBuilderTest extends \PHPUnit_Framework_TestCase
{

    public function testCountableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\Countable::class, new ProductAttributeListBuilder());
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\JsonSerializable::class, new ProductAttributeListBuilder());
    }

    public function testItReturnsTheNumberOfAttributes()
    {
        $attributeArray = [
            'code' => 'foo',
            'contextData' => [],
            'value' => 'bar'
        ];
        $this->assertCount(1, ProductAttributeListBuilder::fromArray([$attributeArray]));
        $this->assertCount(2, ProductAttributeListBuilder::fromArray([$attributeArray, $attributeArray]));
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

        $attributeList = new ProductAttributeListBuilder(
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

        $attributeList = new ProductAttributeListBuilder(ProductAttribute::fromArray($attributeArray));
        
        $attributesWithCode = $attributeList->getAttributesWithCode(AttributeCode::fromString('foo'));
        $this->assertEquals('bar', $attributesWithCode[0]->getValue());
    }

    public function testExceptionIsThrownIfNoAttributeWithGivenCodeIsSet()
    {
        $this->setExpectedException(ProductAttributeNotFoundException::class);
        (new ProductAttributeListBuilder())->getAttributesWithCode('foo');
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

        $attributeList = ProductAttributeListBuilder::fromArray($attributeArray);
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

        $attributeList = ProductAttributeListBuilder::fromArray($attributeArray);
        $result = $attributeList->getAttributesWithCode('foo');

        $this->assertCount(count($attributeArray), $result);
        $this->assertContainsOnly(ProductAttribute::class, $result);
    }

    public function testExceptionIsThrownWhenCombiningAttributesWithSameCodeButDifferentContextPartsIntoList()
    {
        $attributeA = ProductAttribute::fromArray([
            'code' => 'attribute_code1',
            'contextData' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            'value' => 'A'
        ]);
        $attributeB = ProductAttribute::fromArray([
            'code' => 'attribute_code2',
            'contextData' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            'value' => 'B'
        ]);
        $attributeC = ProductAttribute::fromArray([
            'code' => 'attribute_code2',
            'contextData' => [
                'foo' => 'bar',
            ],
            'value' => 'C'
        ]);

        $this->setExpectedException(
            ProductAttributeContextPartsMismatchException::class,
            'The attribute "attribute_code2" has multiple values with different contexts ' .
            'which can not be part of one product attribute list'
        );
        new ProductAttributeListBuilder($attributeA, $attributeB, $attributeC);
    }

    public function testAttributeValuesForAGivenContextAreExtracted()
    {
        $contextDataA = ['website' => 'A'];
        $contextDataB = ['website' => 'B'];
        $attributesArray = [
            ['code' => 'foo', 'contextData' => $contextDataA, 'value' => 'expected'],
            ['code' => 'foo', 'contextData' => $contextDataA, 'value' => 'expected'],
            ['code' => 'bar', 'contextData' => $contextDataA, 'value' => 'expected'],
            ['code' => 'foo', 'contextData' => $contextDataB, 'value' => 'not-expected'],
            ['code' => 'buz', 'contextData' => $contextDataB, 'value' => 'not-expected'],
        ];

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('matchesDataSet')->willReturnMap([
            [$contextDataA, true],
            [$contextDataB, false],
        ]);
        $originalAttributeList = ProductAttributeListBuilder::fromArray($attributesArray);
        $matchingAttributeList = $originalAttributeList->getAttributeListForContext($stubContext);
        $this->assertCount(3, $matchingAttributeList);
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
        $attributeCodes = (new ProductAttributeListBuilder(...$attributes))->getAttributeCodes();
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
        $this->assertFalse((new ProductAttributeListBuilder())->hasAttribute('foo'));
    }

    public function testHasAttributeReturnsTrueForAttributesInTheList()
    {
        $attributeArray = [['code' => 'foo', 'contextData' => [], 'value' => 'bar']];
        $attributeList = ProductAttributeListBuilder::fromArray($attributeArray);
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
        $sourceAttributeList = ProductAttributeListBuilder::fromArray($attributesArray);

        $json = json_encode($sourceAttributeList);
        
        $rehydratedAttributeList = ProductAttributeListBuilder::fromArray(json_decode($json, true));
        $this->assertEquals($sourceAttributeList->getAttributeCodes(), $rehydratedAttributeList->getAttributeCodes());
    }
}
