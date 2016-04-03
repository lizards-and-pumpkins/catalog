<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Product\Listing\Exception\DuplicateProductListingAttributeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\InvalidCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\InvalidNumberOfCriteriaXmlNodesException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\MissingCriterionAttributeNameXmlAttributeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\MissingProductListingAttributeNameXmlAttributeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\MissingTypeXmlAttributeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\MissingCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\MissingUrlKeyXmlAttributeException;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListing
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\UrlKey\UrlKey
 * @uses   \LizardsAndPumpkins\Import\XPathParser
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
