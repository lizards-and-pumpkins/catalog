<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;

/**
 * @covers \Brera\Product\ProductListingSourceBuilder
 * @uses   \Brera\DataPool\SearchEngine\SearchCriteria
 * @uses   \Brera\DataPool\SearchEngine\SearchCriterion
 * @uses   \Brera\Product\ProductListingSource
 * @uses   \Brera\Utils\XPathParser
 * @uses   \Brera\UrlKey
 */
class ProductListingSourceBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testProductListingSourceWithAndConditionIsCreatedFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" condition="and" website="ru" locale="en_US">
    <category operation="=">accessories</category>
    <gender operation="=">male</gender>
</listing>
EOX;

        $productListingSource = (new ProductListingSourceBuilder())->createProductListingSourceFromXml($xml);

        $urlKey = $productListingSource->getUrlKey();
        $context = $productListingSource->getContextData();
        $searchCriteria = $productListingSource->getCriteria();
        $criteria = $searchCriteria->getCriteria();

        $this->assertInstanceOf(ProductListingSource::class, $productListingSource);
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

    public function testProductListingSourceWithOrConditionIsCreatedFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" condition="or" website="ru" locale="en_US">
    <category operation="=">accessories</category>
    <gender operation="=">male</gender>
</listing>
EOX;

        $productListingSource = (new ProductListingSourceBuilder())->createProductListingSourceFromXml($xml);
        $searchCriteria = $productListingSource->getCriteria();

        $this->assertTrue($searchCriteria->hasOrCondition());
    }

    public function testExceptionIsThrownIfUrlKeyAttributeIsMissing()
    {
        $this->setExpectedException(MissingUrlKeyXmlAttributeException::class);
        (new ProductListingSourceBuilder())->createProductListingSourceFromXml('<listing />');
    }

    public function testExceptionIsThrownIfConditionAttributeOfListingNodeIsMissing()
    {
        $this->setExpectedException(MissingConditionXmlAttributeException::class);
        (new ProductListingSourceBuilder())->createProductListingSourceFromXml('<listing url_key="foo"/>');
    }

    public function testExceptionIsThrownIfConditionAttributeOfListingNodeIsInvalid()
    {
        $this->setExpectedException(InvalidConditionXmlAttributeException::class);
        (new ProductListingSourceBuilder())->createProductListingSourceFromXml(
            '<listing url_key="foo" condition="bar"/>'
        );
    }

    public function testExceptionIsThrownIfCriterionNodeDoesNotHaveOperationAttribute()
    {
        $this->setExpectedException(MissingCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo" condition="and"><bar /></listing>';

        (new ProductListingSourceBuilder())->createProductListingSourceFromXml($xml);
    }
}
