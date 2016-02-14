<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Product\Exception\DuplicateProductListingAttributeException;
use LizardsAndPumpkins\Product\Exception\InvalidCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\InvalidNumberOfCriteriaXmlNodesException;
use LizardsAndPumpkins\Product\Exception\MissingCriterionAttributeNameXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingProductListingAttributeNameXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingTypeXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingUrlKeyXmlAttributeException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\DataVersion
 * @uses   \LizardsAndPumpkins\Product\ProductListing
 * @uses   \LizardsAndPumpkins\Product\ProductListingAttributeList
 * @uses   \LizardsAndPumpkins\UrlKey
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
 */
class ProductListingBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingBuilder
     */
    private $productListingBuilder;

    /**
     * @var DataVersion
     */
    private $testDataVersion;

    protected function setUp()
    {
        $this->productListingBuilder = new ProductListingBuilder();
        $this->testDataVersion = DataVersion::fromVersionString('-1');
    }

    public function testProductListingWithAndCriteriaTypeIsCreatedFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" website="ru" locale="en_US">
    <criteria type="and">
        <attribute name="category" is="Equal">accessories</attribute>
        <attribute name="gender" is="Equal">male</attribute>
    </criteria>
</listing>
EOX;

        $productListing = $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);

        $expectedUrlKey = 'men-accessories';
        $expectedContextData = ['version' => '-1', 'website' => 'ru', 'locale' => 'en_US'];
        $expectedCriteria = CompositeSearchCriterion::createAnd(
            SearchCriterionEqual::create('category', 'accessories'),
            SearchCriterionEqual::create('gender', 'male')
        );

        $this->assertInstanceOf(ProductListing::class, $productListing);
        $this->assertEquals($expectedUrlKey, $productListing->getUrlKey());
        $this->assertEquals($expectedContextData, $productListing->getContextData());
        $this->assertEquals($expectedCriteria, $productListing->getCriteria());
    }

    public function testProductListingWithOrCriteriaTypeIsCreatedFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" website="ru" locale="en_US">
    <criteria type="or">
        <attribute name="category" is="Equal">accessories</attribute>
        <attribute name="gender" is="Equal">male</attribute>
    </criteria>
</listing>
EOX;

        $productListing = $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);

        $expectedCriteria = CompositeSearchCriterion::createOr(
            SearchCriterionEqual::create('category', 'accessories'),
            SearchCriterionEqual::create('gender', 'male')
        );

        $this->assertEquals($expectedCriteria, $productListing->getCriteria());
    }

    public function testProductListingWithMultiLevelCriteriaIsCreatedFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" website="ru" locale="en_US">
    <criteria type="and">
        <attribute name="category" is="Equal">accessories</attribute>
        <criteria type="or">
            <attribute name="stock_qty" is="GreaterThan">0</attribute>
            <attribute name="backorders" is="Equal">true</attribute>
        </criteria>
    </criteria>
</listing>
EOX;

        $productListing = $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);

        $expectedCriteria = CompositeSearchCriterion::createAnd(
            SearchCriterionEqual::create('category', 'accessories'),
            CompositeSearchCriterion::createOr(
                SearchCriterionGreaterThan::create('stock_qty', 0),
                SearchCriterionEqual::create('backorders', 'true')
            )
        );

        $this->assertEquals($expectedCriteria, $productListing->getCriteria());
    }

    public function testExceptionIsThrownIfUrlKeyAttributeIsMissing()
    {
        $this->expectException(MissingUrlKeyXmlAttributeException::class);
        $xml = '<listing />';
        $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriteriaNodeDoesNotExist()
    {
        $this->expectException(InvalidNumberOfCriteriaXmlNodesException::class);
        $xml = '<listing url_key="foo"/>';
        $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfTypeAttributeOfListingNodeIsMissing()
    {
        $this->expectException(MissingTypeXmlAttributeException::class);
        $xml = '<listing url_key="foo"><criteria/></listing>';
        $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriterionNodeDoesNotHaveAttributeName()
    {
        $this->expectException(MissingCriterionAttributeNameXmlAttributeException::class);
        $xml = '<listing url_key="foo"><criteria type="and"><attribute/></criteria></listing>';
        $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriterionNodeDoesNotHaveOperationAttribute()
    {
        $this->expectException(MissingCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo"><criteria type="and"><attribute name="bar"/></criteria></listing>';
        $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriterionOperationAttributeIsNotAValidClass()
    {
        $this->expectException(InvalidCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo"><criteria type="and"><attribute name="bar" is="baz"/></criteria></listing>';
        $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfNameAttributeIsMissingInProductListingAttributeNode()
    {
        $this->expectException(MissingProductListingAttributeNameXmlAttributeException::class);
        $xml = <<<EOX
<listing url_key="men-accessories" website="ru" locale="en_US">
    <criteria type="and">
        <attribute name="category" is="Equal">accessories</attribute>
    </criteria>
    <attributes>
        <attribute>foo</attribute>
    </attributes>
</listing>
EOX;
        $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testProductListingWithGivenAttributeIsReturned()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" website="ru" locale="en_US">
    <criteria type="and">
        <attribute name="category" is="Equal">accessories</attribute>
    </criteria>
    <attributes>
        <attribute name="foo">bar</attribute>
    </attributes>
</listing>
EOX;
        $productListing = $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);

        $this->assertTrue($productListing->hasAttribute('foo'));
        $this->assertSame('bar', $productListing->getAttributeValueByCode('foo'));
    }

    public function testExceptionIsThrownIfSameAttributeIsSpecifiedMoreThenOnceForTheSameListing()
    {
        $this->expectException(DuplicateProductListingAttributeException::class);

        $xml = <<<EOX
<listing url_key="men-accessories" website="ru" locale="en_US">
    <criteria type="and">
        <attribute name="category" is="Equal">accessories</attribute>
    </criteria>
    <attributes>
        <attribute name="foo">bar</attribute>
        <attribute name="foo">baz</attribute>
    </attributes>
</listing>
EOX;
        $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }
}
