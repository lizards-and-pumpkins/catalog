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
        $this->attributeList = new ProductAttributeList();
    }

    /**
     * @test
     */
    public function itShouldAddAndGetAttributeFromAProductAttributeList()
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

    /**
     * @test
     * @expectedException \Brera\Product\ProductAttributeNotFoundException
     */
    public function itShouldThrownAnExceptionIfBlankCodeIsProvided()
    {
        $this->attributeList->getAttribute('');
    }

    /**
     * @test
     * @expectedException \Brera\Product\ProductAttributeNotFoundException
     */
    public function itShouldThrownAnExceptionIfNoAttributeWithGivenCodeIsSet()
    {
        $this->attributeList->getAttribute('foo');
    }

    /**
     * @test
     */
    public function itShouldCreateAttributeListFromAttributesArray()
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

    /**
     * @test
     */
    public function itShouldReturnAnAttributeList()
    {
        $attributeArray = [
            [
                'nodeName'      => 'name',
                'attributes'    => ['website' => 'test'],
                'value'         => 'foo'
            ]
        ];

        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $stubContext = $this->getMock(Context::class);
        $this->assertInstanceOf(
            ProductAttributeList::class,
            $attributeList->getAttributesForContext($stubContext)
        );
    }

    /**
     * @test
     * @dataProvider extractAttributesDataProvider
     */
    public function itShouldExtractAttributeValuesForAGivenContext(
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
        $stubContext = $this->getStubContextWithReturnValueMap($contextReturnValueMap);
        $resultList = $attributeList->getAttributesForContext($stubContext);

        $this->assertEquals($expected, $resultList->getAttribute($attributeCode)->getValue());
    }

    /**
     * @return mixed[]
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

    /**
     * @param array $returnValueMap
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubContextWithReturnValueMap(array $returnValueMap)
    {
        $stubContext = $this->getMock(Context::class);
        $stubContext->expects($this->any())
            ->method('getSupportedCodes')
            ->willReturn(array_column($returnValueMap, 0));
        $stubContext->expects($this->any())
            ->method('getValue')
            ->willReturnMap($returnValueMap);
        return $stubContext;
    }
}
