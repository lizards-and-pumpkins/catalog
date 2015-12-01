<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Product\Exception\DataNotStringException;
use LizardsAndPumpkins\Product\Exception\InvalidConditionXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\InvalidCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingConditionXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingUrlKeyXmlAttributeException;
use LizardsAndPumpkins\UrlKey;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingCriteriaBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\Product\ProductListingCriteria
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
 * @uses   \LizardsAndPumpkins\UrlKey
 * @uses   \LizardsAndPumpkins\DataVersion
 */
class ProductListingCriteriaBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataVersion
     */
    private $testDataVersion;

    protected function setUp()
    {
        $this->testDataVersion = DataVersion::fromVersionString('-1');
    }

    public function testProductListingCriteriaWithAndConditionIsCreatedFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" condition="and" website="ru" locale="en_US">
    <category operation="Equal">accessories</category>
    <gender operation="Equal">male</gender>
</listing>
EOX;

        $productListingCriteria = (new ProductListingCriteriaBuilder())
            ->createProductListingCriteriaFromXml($xml, $this->testDataVersion);

        $urlKey = $productListingCriteria->getUrlKey();
        $context = $productListingCriteria->getContextData();
        $result = $productListingCriteria->getCriteria();

        $this->assertInstanceOf(ProductListingCriteria::class, $productListingCriteria);
        $this->assertEquals('men-accessories', $urlKey);
        $this->assertEquals(['version' => '-1', 'website' => 'ru', 'locale' => 'en_US'], $context);

        $expectedCriteria = CompositeSearchCriterion::createAnd(
            SearchCriterionEqual::create('category', 'accessories'),
            SearchCriterionEqual::create('gender', 'male')
        );

        $this->assertEquals($expectedCriteria, $result);
    }

    public function testProductListingCriteriaWithOrConditionIsCreatedFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" condition="or" website="ru" locale="en_US">
    <category operation="Equal">accessories</category>
    <gender operation="Equal">male</gender>
</listing>
EOX;

        $productListingCriteria = (new ProductListingCriteriaBuilder())
            ->createProductListingCriteriaFromXml($xml, $this->testDataVersion);
        $result = $productListingCriteria->getCriteria();

        $expectedCriteria = CompositeSearchCriterion::createOr(
            SearchCriterionEqual::create('category', 'accessories'),
            SearchCriterionEqual::create('gender', 'male')
        );

        $this->assertEquals($expectedCriteria, $result);
    }

    public function testExceptionIsThrownIfUrlKeyAttributeIsMissing()
    {
        $this->setExpectedException(MissingUrlKeyXmlAttributeException::class);
        $xml = '<listing />';
        (new ProductListingCriteriaBuilder())->createProductListingCriteriaFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfConditionAttributeOfListingNodeIsMissing()
    {
        $this->setExpectedException(MissingConditionXmlAttributeException::class);
        $xml = '<listing url_key="foo"/>';
        (new ProductListingCriteriaBuilder())->createProductListingCriteriaFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfConditionAttributeOfListingNodeIsInvalid()
    {
        $this->setExpectedException(InvalidConditionXmlAttributeException::class);
        $xml = '<listing url_key="foo" condition="bar"/>';
        (new ProductListingCriteriaBuilder())->createProductListingCriteriaFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriterionNodeDoesNotHaveOperationAttribute()
    {
        $this->setExpectedException(MissingCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo" condition="and"><bar /></listing>';
        (new ProductListingCriteriaBuilder())->createProductListingCriteriaFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriterionOperationAttributeIsNotAValidClass()
    {
        $this->setExpectedException(InvalidCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo" condition="and"><bar operation="baz" /></listing>';
        (new ProductListingCriteriaBuilder())->createProductListingCriteriaFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriterionOperationAttributeContainsNonCharacterData()
    {
        $this->setExpectedException(
            InvalidCriterionOperationXmlAttributeException::class,
            sprintf('Invalid operation in product listing XML "%s", only the letters a-z are allowed.', "a\\b")
        );
        $xml = '<listing url_key="foo" condition="and"><bar operation="a\\b" /></listing>';
        (new ProductListingCriteriaBuilder())->createProductListingCriteriaFromXml($xml, $this->testDataVersion);
    }

    public function testItThrowsAnExceptionIfTheContextArrayContainsNonStrings()
    {
        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $stubSearchCriteria */
        $stubSearchCriteria = $this->getMock(SearchCriteria::class);
        $expectedMessage = 'The context array has to contain only string values, found ';
        $this->setExpectedException(DataNotStringException::class, $expectedMessage);
        (new ProductListingCriteriaBuilder())->createProductListingCriteria(
            UrlKey::fromString('http://example.com'),
            ['key' => 123],
            $stubSearchCriteria
        );
    }

    public function testItThrowsAnExceptionIfTheContextArrayKeysAreNotStrings()
    {
        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $stubSearchCriteria */
        $stubSearchCriteria = $this->getMock(SearchCriteria::class);
        $expectedMessage = 'The context array has to contain only string keys, found ';
        $this->setExpectedException(DataNotStringException::class, $expectedMessage);
        (new ProductListingCriteriaBuilder())->createProductListingCriteria(
            UrlKey::fromString('http://example.com'),
            [0 => 'value'],
            $stubSearchCriteria
        );
    }
}
