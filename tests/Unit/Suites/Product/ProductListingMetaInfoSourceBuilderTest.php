<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;

/**
 * @covers \Brera\Product\ProductListingMetaInfoSourceBuilder
 * @uses   \Brera\DataPool\SearchEngine\SearchCriteria
 * @uses   \Brera\DataPool\SearchEngine\SearchCriterion
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
    <category operation="=">accessories</category>
    <gender operation="=">male</gender>
</listing>
EOX;

        $productListingMetaInfoSource = (new ProductListingMetaInfoSourceBuilder())
            ->createProductListingMetaInfoSourceFromXml($xml);

        $urlKey = $productListingMetaInfoSource->getUrlKey();
        $context = $productListingMetaInfoSource->getContextData();
        $searchCriteria = $productListingMetaInfoSource->getCriteria();
        $criteria = $searchCriteria->getCriteria();

        $this->assertInstanceOf(ProductListingMetaInfoSource::class, $productListingMetaInfoSource);
        $this->assertEquals('men-accessories', $urlKey);
        $this->assertEquals(['website' => 'ru', 'locale' => 'en_US'], $context);

        $expectedCriterion1 = SearchCriterion::create('category', 'accessories', '=');
        $expectedCriterion2 = SearchCriterion::create('gender', 'male', '=');

        $this->assertInstanceOf(SearchCriteria::class, $searchCriteria);
        $this->assertTrue($searchCriteria->hasAndCondition());
        $this->assertCount(2, $criteria);
        $this->assertEquals($expectedCriterion1, $criteria[0]);
        $this->assertEquals($expectedCriterion2, $criteria[1]);
    }

    public function testProductListingMetaInfoSourceWithOrConditionIsCreatedFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" condition="or" website="ru" locale="en_US">
    <category operation="=">accessories</category>
    <gender operation="=">male</gender>
</listing>
EOX;

        $productListingMetaInfoSource = (new ProductListingMetaInfoSourceBuilder())
            ->createProductListingMetaInfoSourceFromXml($xml);
        $searchCriteria = $productListingMetaInfoSource->getCriteria();

        $this->assertTrue($searchCriteria->hasOrCondition());
    }

    public function testExceptionIsThrownIfUrlKeyAttributeIsMissing()
    {
        $this->setExpectedException(MissingUrlKeyXmlAttributeException::class);
        (new ProductListingMetaInfoSourceBuilder())->createProductListingMetaInfoSourceFromXml('<listing />');
    }

    public function testExceptionIsThrownIfConditionAttributeOfListingNodeIsMissing()
    {
        $this->setExpectedException(MissingConditionXmlAttributeException::class);
        (new ProductListingMetaInfoSourceBuilder())
            ->createProductListingMetaInfoSourceFromXml('<listing url_key="foo"/>');
    }

    public function testExceptionIsThrownIfConditionAttributeOfListingNodeIsInvalid()
    {
        $this->setExpectedException(InvalidConditionXmlAttributeException::class);
        (new ProductListingMetaInfoSourceBuilder())->createProductListingMetaInfoSourceFromXml(
            '<listing url_key="foo" condition="bar"/>'
        );
    }

    public function testExceptionIsThrownIfCriterionNodeDoesNotHaveOperationAttribute()
    {
        $this->setExpectedException(MissingCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo" condition="and"><bar /></listing>';

        (new ProductListingMetaInfoSourceBuilder())->createProductListingMetaInfoSourceFromXml($xml);
    }
}
