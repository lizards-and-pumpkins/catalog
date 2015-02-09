<?php

namespace Brera\Product;

use Brera\Environment\Environment;

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
            'attributes' => ['code' => 'foo'],
            'value' => 'bar'
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
        $attributeArray = [
            [
                'attributes' => ['code' => 'name'],
                'value' => 'bar'
            ]
        ];

        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $attribute = $attributeList->getAttribute('name');

        $this->assertEquals('bar', $attribute->getValue());
    }

    /**
     * @test
     */
    public function itShouldReturnAnAttributeList()
    {
        $attributeArray = [
            [
                'attributes' => ['code' => 'name', 'website' => 'test'],
                'value' => 'foo'
            ]
        ];

        $attributeList = ProductAttributeList::fromArray($attributeArray);
        $stubEnvironment = $this->getMock(Environment::class);
        $this->assertInstanceOf(
            ProductAttributeList::class,
            $attributeList->getAttributesForEnvironment($stubEnvironment)
        );
    }

    /**
     * @test
     * @dataProvider extractAttributesDataProvider
     */
    public function itShouldExtractAttributeValuesForAGivenEnvironment(
        $websiteCodeA, $websiteCodeB, $websiteCodeC,
        $langA, $langB, $langC,
        $valueA, $valueB, $valueC,
        $environmentReturnValueMap,
        $expected
    )
    {
        $attributeCode = 'name';
        $attributesArray = [
            [
                'attributes' => ['code' => $attributeCode, 'website' => $websiteCodeA, 'language' => $langA],
                'value' => $valueA
            ],
            [
                'attributes' => ['code' => $attributeCode, 'website' => $websiteCodeB, 'language' => $langB],
                'value' => $valueB
            ],
            [
                'attributes' => ['code' => $attributeCode, 'website' => $websiteCodeC, 'language' => $langC],
                'value' => $valueC
            ],
        ];
        $attributeList = ProductAttributeList::fromArray($attributesArray);
        $stubEnvironment = $this->getStubEnvironmentWithReturnValueMap($environmentReturnValueMap);
        $resultList = $attributeList->getAttributesForEnvironment($stubEnvironment);
        $this->assertEquals($expected, $resultList->getAttribute($attributeCode)->getValue());
    }

    public function extractAttributesDataProvider()
    {
        return [
            'only-web-in-env' => [
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
    private function getStubEnvironmentWithReturnValueMap(array $returnValueMap)
    {
        $stubEnvironment = $this->getMock(Environment::class);
        $stubEnvironment->expects($this->any())
            ->method('getSupportedCodes')
            ->willReturn(array_column($returnValueMap, 0));
        $stubEnvironment->expects($this->any())
            ->method('getValue')
            ->willReturnMap($returnValueMap);
        return $stubEnvironment;
    }
}
