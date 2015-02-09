<?php

namespace Brera\Product;

use Brera\Environment\Environment;

/**
 * @covers \Brera\Product\ProductAttribute
 * @uses \Brera\Product\ProductAttributeList
 */
class ProductAttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnTrueIfAttributeWithGivenCodeExists()
    {
        $attribute = ProductAttribute::fromArray([
        'nodeName'      => 'foo',
        'attributes'    => [],
        'value'         => 'bar'
        ]);

        $this->assertTrue($attribute->isCodeEqualsTo('foo'));
    }

    /**
     * @test
     */
    public function itShouldReturnFalseIfAttributeWithGivenCodeDoesNotExist()
    {
        $attribute = ProductAttribute::fromArray([
        'nodeName'      => 'foo',
        'attributes'    => [],
        'value'         => 'bar'
        ]);

        $this->assertFalse($attribute->isCodeEqualsTo('baz'));
    }

    /**
     * @test
     */
    public function itShouldReturnAttributeCode()
    {
        $attribute = ProductAttribute::fromArray([
        'nodeName'      => 'foo',
        'attributes'    => [],
        'value'         => 'bar'
        ]);

        $this->assertEquals('foo', $attribute->getCode());
    }

    /**
     * @test
     */
    public function itShouldReturnAttributeValue()
    {
        $attribute = ProductAttribute::fromArray([
        'nodeName'      => 'foo',
        'attributes'    => [],
        'value'         => 'bar'
        ]);

        $this->assertEquals('bar', $attribute->getValue());
    }

    /**
     * @test
     */
    public function itShouldReturnAttributeWithSubAttribute()
    {
        $attribute = ProductAttribute::fromArray([
        'nodeName'      => 'foo',
        'attributes'    => [],
        'value'         => [
        [
        'nodeName'      => 'bar',
        'attributes'    => [],
        'value'         => 1
        ],
        [
        'nodeName'      => 'baz',
        'attributes'    => [],
        'value'         => 2
        ]
        ]
        ]);

        /** @var ProductAttributeList $attributeValue */
        $attributeValue = $attribute->getValue();

        $this->assertInstanceOf(ProductAttributeList::class, $attributeValue);
        $this->assertEquals(1, $attributeValue->getAttribute('bar')->getValue());
        $this->assertEquals(2, $attributeValue->getAttribute('baz')->getValue());
    }

    /**
     * @param $attributeEnvironment
     * @return ProductAttribute
     */
    private function createProductAttributeWithArray(array $attributeEnvironment)
    {
        return ProductAttribute::fromArray([
        'nodeName'      => 'name',
        'attributes'    => $attributeEnvironment,
        'value'         => 'dummy-test-value'
        ]);
    }

    /**
     * @param $returnValueMap
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getEnvironmentMockWithReturnValueMap(array $returnValueMap)
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

    /**
     * @test
     */
    public function itShouldReturnAnIntegerMatchScore()
    {
        $attribute = $this->createProductAttributeWithArray([]);
        $stubEnvironment = $this->getEnvironmentMockWithReturnValueMap([]);
        $this->assertInternalType('int', $attribute->getMatchScoreForEnvironment($stubEnvironment));
    }

    /**
     * @test
     */
    public function itShouldReturn1ForTheMatchScoreForAnEnvironmentWith1Match()
    {
        $testWebsiteCode = 'foo';
        $attribute = $this->createProductAttributeWithArray(['website' => $testWebsiteCode, 'language' => 'bar']);
        $stubEnvironment = $this->getEnvironmentMockWithReturnValueMap([
        ['website', $testWebsiteCode],
        ['version', '1'],
        ]);
        $this->assertSame(1, $attribute->getMatchScoreForEnvironment($stubEnvironment));
    }

    /**
     * @test
     */
    public function itShouldReturn2ForTheMatchScoreForAnEnvironmentWith2Matches()
    {
        $testWebsiteCode = 'foo';
        $testLanguageCode = 'bar';
        $attribute = $this->createProductAttributeWithArray([
        'website' => $testWebsiteCode, 'language' => $testLanguageCode
        ]);
        $stubEnvironment = $this->getEnvironmentMockWithReturnValueMap([
        ['website', $testWebsiteCode],
        ['language', $testLanguageCode],
        ['version', '1'],
        ]);
        $this->assertSame(2, $attribute->getMatchScoreForEnvironment($stubEnvironment));
    }

    /**
     * @test
     */
    public function itShouldReturn0ForTheMatchScoreForAnEnvironmentWithNoMatches()
    {
        $attribute = $this->createProductAttributeWithArray(['website' => 'foo', 'language' => 'bar']);
        $stubEnvironment = $this->getEnvironmentMockWithReturnValueMap([
        ['website', 'buz'],
        ['language', 'qux'],
        ['version', '1'],
        ]);
        $this->assertSame(0, $attribute->getMatchScoreForEnvironment($stubEnvironment));
    }

    /**
     * @test
     */
    public function itShouldReturn1ForTheMatchScoreForAnEnvironmentWith1MatchAnd1Miss()
    {
        $testLanguageCode = 'bar';
        $attribute = $this->createProductAttributeWithArray(['website' => 'foo', 'language' => $testLanguageCode]);
        $stubEnvironment = $this->getEnvironmentMockWithReturnValueMap([
        ['website', 'buz'],
        ['language', $testLanguageCode],
        ['version', '1'],
        ]);
        $this->assertSame(1, $attribute->getMatchScoreForEnvironment($stubEnvironment));
    }
}
