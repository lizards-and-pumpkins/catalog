<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;

/**
 * @covers \Brera\Product\ProductListingSourceBuilder
 * @uses   \Brera\Utils\XPathParser
 * @uses   \Brera\Product\ProductListingSource
 * @uses   \Brera\DataPool\SearchEngine\SearchCriteria
 * @uses   \Brera\DataPool\SearchEngine\SearchCriterion
 */
class ProductListingSourceBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testProductListingSourceIsCreatedFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" condition="and" website="ru" language="en_US">
    <category operation="eq">accessories</category>
    <gender operation="eq">male</gender>
</listing>
EOX;

        $productListingSource = (new ProductListingSourceBuilder())->createProductListingSourceFromXml($xml);

        $urlKey = $productListingSource->getUrlKey();
        $context = $productListingSource->getContextData();
        $searchCriteria = $productListingSource->getCriteria();
        $criteria = $searchCriteria->getCriteria();

        $this->assertInstanceOf(ProductListingSource::class, $productListingSource);
        $this->assertEquals('men-accessories', $urlKey);
        $this->assertEquals(['website' => 'ru', 'language' => 'en_US'], $context);

        $expectedCriterion1 = SearchCriterion::create('category', 'accessories', 'eq');
        $expectedCriterion2 = SearchCriterion::create('gender', 'male', 'eq');

        $this->assertInstanceOf(SearchCriteria::class, $searchCriteria);
        $this->assertTrue($searchCriteria->hasAndCondition());
        $this->assertCount(2, $criteria);
        $this->assertEquals($expectedCriterion1, $criteria[0]);
        $this->assertEquals($expectedCriterion2, $criteria[1]);
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

    public function testExceptionIsThrownIfCriterionNodeDoesNotHaveOperationAttribute()
    {
        $this->setExpectedException(MissingCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo" condition="and"><bar /></listing>';

        (new ProductListingSourceBuilder())->createProductListingSourceFromXml($xml);
    }
}
