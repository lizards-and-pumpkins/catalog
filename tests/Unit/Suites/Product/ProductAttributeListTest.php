<?php

namespace Brera\Product;

use Brera\Context\Context;

/**
 * @covers \Brera\Product\ProductAttributeList
 * @uses   \Brera\Product\ProductAttribute
 */
class ProductAttributeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductAttributeList
     */
    private $attributeList;

    protected function setUp()
    {
        $this->attributeList = new ProductAttributeList;
    }

    public function testAttributeIsAddedAndRetrievedFromProductAttributeList()
    {
        $attributeArray = [
            'nodeName'      => 'foo',
            'attributes'    => [],
            'value'         => 'bar'
        ];

        $attribute = ProductAttribute::fromArray($attributeArray);

        $this->attributeList->add($attribute);
        $result = $this->attributeList->getAttribute('foo');

        $this->assertEquals('bar', $result->getValue());
    }

    public function testExceptionIsThrownIfBlankCodeIsProvided()
    {
        $this->setExpectedException(ProductAttributeNotFoundException::class);
        $this->attributeList->getAttribute('');
    }

    public function testExceptionIsThrownIfNoAttributeWithGivenCodeIsSet()
    {
        $this->setExpectedException(ProductAttributeNotFoundException::class);
        $this->attributeList->getAttribute('foo');
    }

    public function testAttributeListIsCreatedFromAttributesArray()
    {
        $attributeArray = [[
            'nodeName'      => 'foo',
            'attributes'    => [],
            'value'         => 'bar'
        ]];

        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $attribute = $attributeList->getAttribute('foo');

        $this->assertEquals('bar', $attribute->getValue());
    }

    public function testAttributeListIsReturned()
    {
        $attributeArray = [[
            'nodeName'      => 'name',
            'attributes'    => ['website' => 'test'],
            'value'         => 'foo'
        ]];

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $this->assertInstanceOf(
            ProductAttributeList::class,
            $attributeList->getAttributesForContext($stubContext)
        );
    }

    /**
     * @dataProvider extractAttributesDataProvider
     * @param string $websiteCodeA
     * @param string $websiteCodeB
     * @param string $websiteCodeC
     * @param string $langA
     * @param string $langB
     * @param string $langC
     * @param string $valueA
     * @param string $valueB
     * @param string $valueC
     * @param string[] $contextReturnValueMap
     * @param string $expected
     */
    public function testAttributeValuesForAGivenContextAreExtracted(
        $websiteCodeA,
        $websiteCodeB,
        $websiteCodeC,
        $langA,
        $langB,
        $langC,
        $valueA,
        $valueB,
        $valueC,
        $contextReturnValueMap,
        $expected
    ) {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getStubContextWithReturnValueMap($contextReturnValueMap);
        $attributeCode = 'name';
        $attributesArray = [
            [
                'nodeName'      => $attributeCode,
                'attributes'    => ['website' => $websiteCodeA, 'language' => $langA],
                'value'         => $valueA
            ],
            [
                'nodeName'      => $attributeCode,
                'attributes'    => ['website' => $websiteCodeB, 'language' => $langB],
                'value'         => $valueB
            ],
            [
                'nodeName'      => $attributeCode,
                'attributes'    => ['website' => $websiteCodeC, 'language' => $langC],
                'value'         => $valueC
            ],
        ];
        $attributeList = ProductAttributeList::fromArray($attributesArray);
        $resultList = $attributeList->getAttributesForContext($stubContext);

        $this->assertEquals($expected, $resultList->getAttribute($attributeCode)->getValue());
    }

    /**
     * @return array[]
     */
    public function extractAttributesDataProvider()
    {
        return [
            'only-web-in-context' => [
                'webA', 'webB', 'webC', // website codes
                'lang', 'lang', 'lang', // language codes
                'AAA', 'BBB', 'CCC', // attribute values
                [['website', 'webB']], // return value map
                'BBB' // expected value
            ],
            'one-match' => [
                'webA', 'webA', 'webB', // website codes
                'langA', 'langB', 'langA', // language codes
                'AAA', 'BBB', 'CCC', // attribute values
                [['website', 'webB'], ['language', 'langA']], // return value map
                'CCC' // expected value
            ],
            'two-match-pick-first' => [
                'webA', 'webB', 'webC', // website codes
                'langB', 'langA', 'langC', // language codes
                'AAA', 'BBB', 'CCC', // attribute values
                [['website', 'webA'], ['language', 'langA']], // return value map
                'AAA' // expected value
            ],
            '3-match-pick-highest' => [
                'webA', 'webB', 'webA', // website codes
                'langB', 'langA', 'langA', // language codes
                'AAA', 'BBB', 'CCC', // attribute values
                [['website', 'webA'], ['language', 'langA']], // return value map
                'CCC' // expected value
            ],
        ];
    }

    public function testExceptionIsThrownWhileCombiningAttributesWithSameCodeButDifferentContextPartsIntoList()
    {
        $attributeA = ProductAttribute::fromArray([
            'nodeName'   => 'attributeCode',
            'attributes' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            'value'      => 'valueA'
        ]);
        $attributeB = ProductAttribute::fromArray([
            'nodeName'   => 'attributeCode',
            'attributes' => [
                'foo' => 'bar',
            ],
            'value'      => 'valueB'
        ]);

        $this->setExpectedException(AttributeContextPartsMismatchException::class);

        $this->attributeList->add($attributeA);
        $this->attributeList->add($attributeB);
    }

    /**
     * @param mixed[] $returnValueMap
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubContextWithReturnValueMap(array $returnValueMap)
    {
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('getSupportedCodes')
            ->willReturn(array_column($returnValueMap, 0));
        $stubContext->method('getValue')
            ->willReturnMap($returnValueMap);
        return $stubContext;
    }
}
