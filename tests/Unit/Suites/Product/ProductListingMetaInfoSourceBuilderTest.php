<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;

/**
 * @covers \Brera\Product\ProductListingMetaInfoSourceBuilder
 * @uses   \Brera\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \Brera\Product\ProductListingMetaInfoSource
 * @uses   \Brera\Utils\XPathParser
 * @uses   \Brera\UrlKey
 */
class ProductListingMetaInfoSourceBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testProductListingMetaInfoSourceWithAndConditionIsCreatedFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" condition="and" website="ru" locale="en_US">
    <category operation="Equal">accessories</category>
    <gender operation="Equal">male</gender>
</listing>
EOX;

        $productListingMetaInfoSource = (new ProductListingMetaInfoSourceBuilder())
            ->createProductListingMetaInfoSourceFromXml($xml);

        $urlKey = $productListingMetaInfoSource->getUrlKey();
        $context = $productListingMetaInfoSource->getContextData();
        $result = $productListingMetaInfoSource->getCriteria();

        $this->assertInstanceOf(ProductListingMetaInfoSource::class, $productListingMetaInfoSource);
        $this->assertEquals('men-accessories', $urlKey);
        $this->assertEquals(['website' => 'ru', 'locale' => 'en_US'], $context);

        $expectedCriterion1 = SearchCriterionEqual::create('category', 'accessories');
        $expectedCriterion2 = SearchCriterionEqual::create('gender', 'male');
        $expectedCriteria = CompositeSearchCriterion::createAnd($expectedCriterion1, $expectedCriterion2);

        $this->assertEquals($expectedCriteria, $result);
    }

    public function testProductListingMetaInfoSourceWithOrConditionIsCreatedFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" condition="or" website="ru" locale="en_US">
    <category operation="Equal">accessories</category>
    <gender operation="Equal">male</gender>
</listing>
EOX;

        $productListingMetaInfoSource = (new ProductListingMetaInfoSourceBuilder())
            ->createProductListingMetaInfoSourceFromXml($xml);
        $result = $productListingMetaInfoSource->getCriteria();

        $expectedCriterion1 = SearchCriterionEqual::create('category', 'accessories');
        $expectedCriterion2 = SearchCriterionEqual::create('gender', 'male');
        $expectedCriteria = CompositeSearchCriterion::createOr($expectedCriterion1, $expectedCriterion2);

        $this->assertEquals($expectedCriteria, $result);
    }

    public function testExceptionIsThrownIfUrlKeyAttributeIsMissing()
    {
        $this->setExpectedException(MissingUrlKeyXmlAttributeException::class);
        $xml = '<listing />';
        (new ProductListingMetaInfoSourceBuilder())->createProductListingMetaInfoSourceFromXml($xml);
    }

    public function testExceptionIsThrownIfConditionAttributeOfListingNodeIsMissing()
    {
        $this->setExpectedException(MissingConditionXmlAttributeException::class);
        $xml = '<listing url_key="foo"/>';
        (new ProductListingMetaInfoSourceBuilder())->createProductListingMetaInfoSourceFromXml($xml);
    }

    public function testExceptionIsThrownIfConditionAttributeOfListingNodeIsInvalid()
    {
        $this->setExpectedException(InvalidConditionXmlAttributeException::class);
        $xml = '<listing url_key="foo" condition="bar"/>';
        (new ProductListingMetaInfoSourceBuilder())->createProductListingMetaInfoSourceFromXml($xml);
    }

    public function testExceptionIsThrownIfCriterionNodeDoesNotHaveOperationAttribute()
    {
        $this->setExpectedException(MissingCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo" condition="and"><bar /></listing>';
        (new ProductListingMetaInfoSourceBuilder())->createProductListingMetaInfoSourceFromXml($xml);
    }

    public function testExceptionIsThrownIfCriterionOperationAttributeIsInvalid()
    {
        $this->setExpectedException(InvalidCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo" condition="and"><bar operation="baz" /></listing>';
        (new ProductListingMetaInfoSourceBuilder())->createProductListingMetaInfoSourceFromXml($xml);
    }
}
