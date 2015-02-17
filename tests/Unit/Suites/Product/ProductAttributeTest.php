<?php

namespace Brera\Product;

use Brera\Context\Context;

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
     * @param $attributeContext
     * @return ProductAttribute
     */
    private function createProductAttributeWithArray(array $attributeContext)
    {
        return ProductAttribute::fromArray([
        'nodeName'      => 'name',
        'attributes'    => $attributeContext,
        'value'         => 'dummy-test-value'
        ]);
    }

    /**
     * @param $returnValueMap
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getContextMockWithReturnValueMap(array $returnValueMap)
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

    /**
     * @test
     */
    public function itShouldReturnAnIntegerMatchScore()
    {
        $attribute = $this->createProductAttributeWithArray([]);
        $stubContext = $this->getContextMockWithReturnValueMap([]);
        $this->assertInternalType('int', $attribute->getMatchScoreForContext($stubContext));
    }

    /**
     * @test
     */
    public function itShouldReturn1ForTheMatchScoreForAnContextWith1Match()
    {
        $testWebsiteCode = 'foo';
        $attribute = $this->createProductAttributeWithArray(['website' => $testWebsiteCode, 'language' => 'bar']);
        $stubContext = $this->getContextMockWithReturnValueMap([
        ['website', $testWebsiteCode],
        ['version', '1'],
        ]);
        $this->assertSame(1, $attribute->getMatchScoreForContext($stubContext));
    }

    /**
     * @test
     */
    public function itShouldReturn2ForTheMatchScoreForAnContextWith2Matches()
    {
        $testWebsiteCode = 'foo';
        $testLanguageCode = 'bar';
        $attribute = $this->createProductAttributeWithArray([
        'website' => $testWebsiteCode, 'language' => $testLanguageCode
        ]);
        $stubContext = $this->getContextMockWithReturnValueMap([
        ['website', $testWebsiteCode],
        ['language', $testLanguageCode],
        ['version', '1'],
        ]);
        $this->assertSame(2, $attribute->getMatchScoreForContext($stubContext));
    }

    /**
     * @test
     */
    public function itShouldReturn0ForTheMatchScoreForAnContextWithNoMatches()
    {
        $attribute = $this->createProductAttributeWithArray(['website' => 'foo', 'language' => 'bar']);
        $stubContext = $this->getContextMockWithReturnValueMap([
        ['website', 'buz'],
        ['language', 'qux'],
        ['version', '1'],
        ]);
        $this->assertSame(0, $attribute->getMatchScoreForContext($stubContext));
    }

    /**
     * @test
     */
    public function itShouldReturn1ForTheMatchScoreForAnContextWith1MatchAnd1Miss()
    {
        $testLanguageCode = 'bar';
        $attribute = $this->createProductAttributeWithArray(['website' => 'foo', 'language' => $testLanguageCode]);
        $stubContext = $this->getContextMockWithReturnValueMap([
        ['website', 'buz'],
        ['language', $testLanguageCode],
        ['version', '1'],
        ]);
        $this->assertSame(1, $attribute->getMatchScoreForContext($stubContext));
    }
}
