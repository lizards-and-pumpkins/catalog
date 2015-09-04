<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\CompositeSearchCriterion;
use Brera\DataPool\SearchEngine\SearchCriterion;

/**
 * @covers \Brera\Product\ProductListingMetaInfoSourceBuilder
 * @uses   \Brera\DataPool\SearchEngine\CompositeSearchCriterion
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
        $result = $productListingMetaInfoSource->getCriteria();

        $this->assertInstanceOf(ProductListingMetaInfoSource::class, $productListingMetaInfoSource);
        $this->assertEquals('men-accessories', $urlKey);
        $this->assertEquals(['website' => 'ru', 'locale' => 'en_US'], $context);

        $expectedCriteria = CompositeSearchCriterion::createAnd();
        $expectedCriteria->addCriteria(SearchCriterion::create('category', 'accessories', '='));
        $expectedCriteria->addCriteria(SearchCriterion::create('gender', 'male', '='));

        $this->assertEquals($expectedCriteria, $result);
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
        $result = $productListingMetaInfoSource->getCriteria();

        $expectedCriteria = CompositeSearchCriterion::createOr();
        $expectedCriteria->addCriteria(SearchCriterion::create('category', 'accessories', '='));
        $expectedCriteria->addCriteria(SearchCriterion::create('gender', 'male', '='));

        $this->assertEquals($expectedCriteria, $result);
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
