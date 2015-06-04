<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\SearchCriteria;

/**
 * @covers \Brera\Product\ProductListingSourceBuilder
 * @uses   \Brera\Utils\XPathParser
 * @uses   \Brera\Product\ProductListingSource
 * @uses   \Brera\DataPool\SearchEngine\SearchCriteria
 * @uses   \Brera\DataPool\SearchEngine\SearchCriterion
 */
class ProductListingSourceBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldCreateAProductListingSourceFromXml()
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

        $this->assertInstanceOf(SearchCriteria::class, $searchCriteria);
        $this->assertEquals('and', $searchCriteria->getCondition());
        $this->assertCount(2, $criteria);
        $this->assertEquals('category', $criteria[0]->getFieldName());
        $this->assertEquals('accessories', $criteria[0]->getFieldValue());
        $this->assertEquals('eq', $criteria[0]->getOperation());
        $this->assertEquals('gender', $criteria[1]->getFieldName());
        $this->assertEquals('male', $criteria[1]->getFieldValue());
        $this->assertEquals('eq', $criteria[1]->getOperation());
    }

    /**
     * @test
     * @expectedException \Brera\Product\MissingUrlKeyXmlAttributeException
     */
    public function itShouldFailIfUrlKeyAttributeIsMissing()
    {
        $xml = '<listing />';
        (new ProductListingSourceBuilder())->createProductListingSourceFromXml($xml);
    }

    /**
     * @test
     * @expectedException \Brera\Product\MissingConditionXmlAttributeException
     */
    public function itShouldFailIfConditionAttributeOfListingNodeIsMissing()
    {
        $xml = '<listing url_key="foo"/>';
        (new ProductListingSourceBuilder())->createProductListingSourceFromXml($xml);
    }

    /**
     * @test
     * @expectedException \Brera\Product\MissingCriterionOperationXmlAttributeException
     */
    public function itShouldFailIfCriterionNodeDoesNotHaveOperationAttribute()
    {
        $xml = '<listing url_key="foo" condition="and"><bar /></listing>';
        (new ProductListingSourceBuilder())->createProductListingSourceFromXml($xml);
    }
}
