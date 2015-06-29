<?php

namespace Brera\Product;

use Brera\Context\Context;

/**
 * @covers \Brera\Product\ProductAttribute
 * @uses   \Brera\Product\ProductAttributeList
 */
class ProductAttributeTest extends \PHPUnit_Framework_TestCase
{
    public function testTrueIsReturnedIfAttributeWithGivenCodeExists()
    {
        $attribute = ProductAttribute::fromArray([
            'nodeName'   => 'foo',
            'attributes' => [],
            'value'      => 'bar'
        ]);

        $this->assertTrue($attribute->isCodeEqualsTo('foo'));
    }

    public function testFalseIsReturnedIfAttributeWithGivenCodeDoesNotExist()
    {
        $attribute = ProductAttribute::fromArray([
            'nodeName'   => 'foo',
            'attributes' => [],
            'value'      => 'bar'
        ]);

        $this->assertFalse($attribute->isCodeEqualsTo('baz'));
    }

    public function testAttributeCodeIsReturned()
    {
        $attribute = ProductAttribute::fromArray([
            'nodeName'   => 'foo',
            'attributes' => [],
            'value'      => 'bar'
        ]);

        $this->assertEquals('foo', $attribute->getCode());
    }

    public function testAttributeValueIsReturned()
    {
        $attribute = ProductAttribute::fromArray([
            'nodeName'   => 'foo',
            'attributes' => [],
            'value'      => 'bar'
        ]);

        $this->assertEquals('bar', $attribute->getValue());
    }

    public function testAttributeWithSubAttributeIsReturned()
    {
        $attribute = ProductAttribute::fromArray([
            'nodeName'   => 'foo',
            'attributes' => [],
            'value'      => [
                [
                    'nodeName'   => 'bar',
                    'attributes' => [],
                    'value'      => 1
                ],
                [
                    'nodeName'   => 'baz',
                    'attributes' => [],
                    'value'      => 2
                ]
            ]
        ]);

        $attributeValue = $attribute->getValue();

        $this->assertInstanceOf(ProductAttributeList::class, $attributeValue);
        $this->assertEquals(1, $attributeValue->getAttribute('bar')->getValue());
        $this->assertEquals(2, $attributeValue->getAttribute('baz')->getValue());
    }

    public function testIntegerIsReturnedForMatchScore()
    {
        $attribute = $this->createProductAttributeWithArray([]);
        $stubContext = $this->getContextMockWithReturnValueMap([]);
        $this->assertInternalType('int', $attribute->getMatchScoreForContext($stubContext));
    }

    public function testOneIsReturnedForMatchScoreForContextWithOneMatch()
    {
        $testWebsiteCode = 'foo';
        $attribute = $this->createProductAttributeWithArray(['website' => $testWebsiteCode, 'language' => 'bar']);
        $stubContext = $this->getContextMockWithReturnValueMap([
            ['website', $testWebsiteCode],
            ['version', '1'],
        ]);
        $this->assertSame(1, $attribute->getMatchScoreForContext($stubContext));
    }

    public function testTwoIsReturnedForMatchScoreForContextWithTwoMatches()
    {
        $testWebsiteCode = 'foo';
        $testLanguageCode = 'bar';
        $attribute = $this->createProductAttributeWithArray([
            'website'  => $testWebsiteCode,
            'language' => $testLanguageCode
        ]);
        $stubContext = $this->getContextMockWithReturnValueMap([
            ['website', $testWebsiteCode],
            ['language', $testLanguageCode],
            ['version', '1'],
        ]);
        $this->assertSame(2, $attribute->getMatchScoreForContext($stubContext));
    }

    public function testZeroIsReturnedForMatchScoreForContextWithNoMatches()
    {
        $attribute = $this->createProductAttributeWithArray(['website' => 'foo', 'language' => 'bar']);
        $stubContext = $this->getContextMockWithReturnValueMap([
            ['website', 'buz'],
            ['language', 'qux'],
            ['version', '1'],
        ]);
        $this->assertSame(0, $attribute->getMatchScoreForContext($stubContext));
    }

    public function testOneIsReturnedForMatchScoreForContextWithOneMatchAndOneMiss()
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

    /**
     * @param string[] $attributeContext
     * @return ProductAttribute
     */
    private function createProductAttributeWithArray(array $attributeContext)
    {
        return ProductAttribute::fromArray([
            'nodeName'   => 'name',
            'attributes' => $attributeContext,
            'value'      => 'dummy-test-value'
        ]);
    }

    /**
     * @param string[] $returnValueMap
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
}
