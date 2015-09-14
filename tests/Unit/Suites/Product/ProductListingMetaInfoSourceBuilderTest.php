<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use Brera\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterion;
use Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use Brera\Product\Exception\DataNotStringException;
use Brera\Product\Exception\InvalidConditionXmlAttributeException;
use Brera\Product\Exception\InvalidCriterionOperationXmlAttributeException;
use Brera\Product\Exception\MissingConditionXmlAttributeException;
use Brera\Product\Exception\MissingCriterionOperationXmlAttributeException;
use Brera\Product\Exception\MissingUrlKeyXmlAttributeException;
use Brera\UrlKey;

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

    public function testExceptionIsThrownIfCriterionOperationAttributeIsNotAValidClass()
    {
        $this->setExpectedException(InvalidCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo" condition="and"><bar operation="baz" /></listing>';
        (new ProductListingMetaInfoSourceBuilder())->createProductListingMetaInfoSourceFromXml($xml);
    }

    public function testExceptionIsThrownIfCriterionOperationAttributeContainsNonCharacterData()
    {
        $this->setExpectedException(
            InvalidCriterionOperationXmlAttributeException::class,
            sprintf('Invalid operation in product listing XML "%s", only the letters a-z are allowed.', "a\\b")
        );
        $xml = '<listing url_key="foo" condition="and"><bar operation="a\\b" /></listing>';
        (new ProductListingMetaInfoSourceBuilder())->createProductListingMetaInfoSourceFromXml($xml);
    }

    public function testItThrowsAnExceptionIfTheContextArrayContainsNonStrings()
    {
        $expectedMessage = 'The context array has to contain only string values, found ';
        $this->setExpectedException(DataNotStringException::class, $expectedMessage);
        (new ProductListingMetaInfoSourceBuilder())->createProductListingMetaInfoSource(
            UrlKey::fromString('http://example.com'),
            ['key' => 123],
            $this->getMock(SearchCriteria::class)
        );
    }

    public function testItThrowsAnExceptionIfTheContextArrayKeysAreNotStrings()
    {
        $expectedMessage = 'The context array has to contain only string keys, found ';
        $this->setExpectedException(DataNotStringException::class, $expectedMessage);
        (new ProductListingMetaInfoSourceBuilder())->createProductListingMetaInfoSource(
            UrlKey::fromString('http://example.com'),
            [0 => 'value'],
            $this->getMock(SearchCriteria::class)
        );
    }
}
