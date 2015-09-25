<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Exception\ProductAttributeContextPartsMismatchException;
use LizardsAndPumpkins\Product\Exception\ProductAttributeNotFoundException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 */
class ProductAttributeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductAttributeList
     */
    private $attributeList;

    protected function setUp()
    {
        $this->attributeList = new ProductAttributeList();
    }

    public function testCountableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\Countable::class, $this->attributeList);
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->attributeList);
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

    public function testAttributeIsAddedAndRetrievedFromProductAttributeList()
    {
        $attributeArray = [
            'code' => 'foo',
            'contextData' => [],
            'value' => 'bar'
        ];

        $attribute = ProductAttribute::fromArray($attributeArray);

        $this->attributeList->add($attribute);
        $attributesWithCode = $this->attributeList->getAttributesWithCode('foo');
        $result = $attributesWithCode[0];

        $this->assertEquals('bar', $result->getValue());
    }

    public function testExceptionIsThrownIfBlankCodeIsProvided()
    {
        $this->setExpectedException(ProductAttributeNotFoundException::class);
        $this->attributeList->getAttributesWithCode('');
    }

    public function testExceptionIsThrownIfNoAttributeWithGivenCodeIsSet()
    {
        $this->setExpectedException(ProductAttributeNotFoundException::class);
        $this->attributeList->getAttributesWithCode('foo');
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
        $result = $attributesWithCode[0];

        $this->assertEquals('bar', $result->getValue());
    }

    public function testAttributeListContainsMultipleAttributeValues()
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
        $originalAttributeList = ProductAttributeList::fromArray($attributesArray);
        $matchingAttributeList = $originalAttributeList->getAttributeListForContext($stubContext);
        $this->assertCount(3, $matchingAttributeList);
    }

    public function testExceptionIsThrownWhileCombiningAttributesWithSameCodeButDifferentContextPartsIntoList()
    {
        $attributeA = ProductAttribute::fromArray([
            'code' => 'attributeCode',
            'contextData' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            'value' => 'valueA'
        ]);
        $attributeB = ProductAttribute::fromArray([
            'code' => 'attributeCode',
            'contextData' => [
                'foo' => 'bar',
            ],
            'value' => 'valueB'
        ]);

        $this->setExpectedException(ProductAttributeContextPartsMismatchException::class);

        $this->attributeList->add($attributeA);
        $this->attributeList->add($attributeB);
    }

    /**
     * @param int $numAttributesToAdd
     * @param string[] $expected
     * @dataProvider numberOfAttributesToAddProvider
     */
    public function testItReturnsTheCodesOfAttributesInTheList($numAttributesToAdd, $expected)
    {
        for ($i = 0; $i < $numAttributesToAdd; $i++) {
            $this->attributeList->add(ProductAttribute::fromArray([
                'code' => 'attr_' . ($i + 1),
                'contextData' => [],
                'value' => 'value'
            ]));
        }
        $this->assertSame($expected, $this->attributeList->getAttributeCodes());
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
        $this->assertFalse($this->attributeList->hasAttribute('foo'));
    }

    public function testHasAttributeReturnsTrueForAttributesInTheList()
    {
        $attributeArray = [['code' => 'foo', 'contextData' => [], 'value' => 'bar']];
        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $this->assertTrue($attributeList->hasAttribute('foo'));
    }

    public function testArrayRepresentationOfAttributeListIsReturned()
    {
        $attributeArray = [
            'code' => 'foo',
            'contextData' => [],
            'value' => 'bar'
        ];
        $attribute = ProductAttribute::fromArray($attributeArray);
        $this->attributeList->add($attribute);

        $result = $this->attributeList->jsonSerialize();
        $expectedResult = ['foo' => ['bar']];

        $this->assertSame($expectedResult, $result);
    }
}
