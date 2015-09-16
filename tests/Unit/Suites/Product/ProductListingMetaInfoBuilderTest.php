<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\Product\Exception\DataNotStringException;
use LizardsAndPumpkins\Product\Exception\InvalidConditionXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\InvalidCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingConditionXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingUrlKeyXmlAttributeException;
use LizardsAndPumpkins\UrlKey;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingMetaInfoSourceBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\Product\ProductListingMetaInfoSource
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
 * @uses   \LizardsAndPumpkins\UrlKey
 */
class ProductListingMetaInfoBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testProductListingMetaInfoSourceWithAndConditionIsCreatedFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" condition="and" website="ru" locale="en_US">
    <category operation="Equal">accessories</category>
    <gender operation="Equal">male</gender>
</listing>
EOX;

        $productListingMetaInfoSource = (new ProductListingMetaInfoBuilder())
            ->createProductListingMetaInfoSourceFromXml($xml);

        $urlKey = $productListingMetaInfoSource->getUrlKey();
        $context = $productListingMetaInfoSource->getContextData();
        $result = $productListingMetaInfoSource->getCriteria();

        $this->assertInstanceOf(ProductListingMetaInfo::class, $productListingMetaInfoSource);
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

        $productListingMetaInfoSource = (new ProductListingMetaInfoBuilder())
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
        (new ProductListingMetaInfoBuilder())->createProductListingMetaInfoSourceFromXml($xml);
    }

    public function testExceptionIsThrownIfConditionAttributeOfListingNodeIsMissing()
    {
        $this->setExpectedException(MissingConditionXmlAttributeException::class);
        $xml = '<listing url_key="foo"/>';
        (new ProductListingMetaInfoBuilder())->createProductListingMetaInfoSourceFromXml($xml);
    }

    public function testExceptionIsThrownIfConditionAttributeOfListingNodeIsInvalid()
    {
        $this->setExpectedException(InvalidConditionXmlAttributeException::class);
        $xml = '<listing url_key="foo" condition="bar"/>';
        (new ProductListingMetaInfoBuilder())->createProductListingMetaInfoSourceFromXml($xml);
    }

    public function testExceptionIsThrownIfCriterionNodeDoesNotHaveOperationAttribute()
    {
        $this->setExpectedException(MissingCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo" condition="and"><bar /></listing>';
        (new ProductListingMetaInfoBuilder())->createProductListingMetaInfoSourceFromXml($xml);
    }

    public function testExceptionIsThrownIfCriterionOperationAttributeIsNotAValidClass()
    {
        $this->setExpectedException(InvalidCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo" condition="and"><bar operation="baz" /></listing>';
        (new ProductListingMetaInfoBuilder())->createProductListingMetaInfoSourceFromXml($xml);
    }

    public function testExceptionIsThrownIfCriterionOperationAttributeContainsNonCharacterData()
    {
        $this->setExpectedException(
            InvalidCriterionOperationXmlAttributeException::class,
            sprintf('Invalid operation in product listing XML "%s", only the letters a-z are allowed.', "a\\b")
        );
        $xml = '<listing url_key="foo" condition="and"><bar operation="a\\b" /></listing>';
        (new ProductListingMetaInfoBuilder())->createProductListingMetaInfoSourceFromXml($xml);
    }

    public function testItThrowsAnExceptionIfTheContextArrayContainsNonStrings()
    {
        $expectedMessage = 'The context array has to contain only string values, found ';
        $this->setExpectedException(DataNotStringException::class, $expectedMessage);
        (new ProductListingMetaInfoBuilder())->createProductListingMetaInfoSource(
            UrlKey::fromString('http://example.com'),
            ['key' => 123],
            $this->getMock(SearchCriteria::class)
        );
    }

    public function testItThrowsAnExceptionIfTheContextArrayKeysAreNotStrings()
    {
        $expectedMessage = 'The context array has to contain only string keys, found ';
        $this->setExpectedException(DataNotStringException::class, $expectedMessage);
        (new ProductListingMetaInfoBuilder())->createProductListingMetaInfoSource(
            UrlKey::fromString('http://example.com'),
            [0 => 'value'],
            $this->getMock(SearchCriteria::class)
        );
    }
}
