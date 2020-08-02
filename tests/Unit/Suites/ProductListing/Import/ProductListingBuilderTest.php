<?php

declare(strict_types=1);

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
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListing
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\UrlKey\UrlKey
 * @uses   \LizardsAndPumpkins\Import\XPathParser
 */
class ProductListingBuilderTest extends TestCase
{
    /**
     * @var ProductListingBuilder
     */
    private $productListingBuilder;

    /**
     * @var DataVersion
     */
    private $testDataVersion;

    final protected function setUp(): void
    {
        $this->productListingBuilder = new ProductListingBuilder();
        $this->testDataVersion = DataVersion::fromVersionString('-1');
    }

    public function testProductListingWithAndCriteriaTypeIsCreatedFromXml(): void
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
            new SearchCriterionEqual('category', 'accessories'),
            new SearchCriterionEqual('gender', 'male')
        );

        $this->assertInstanceOf(ProductListing::class, $productListing);
        $this->assertEquals($expectedUrlKey, $productListing->getUrlKey());
        $this->assertEquals($expectedContextData, $productListing->getContextData());
        $this->assertEquals($expectedCriteria, $productListing->getCriteria());
    }

    public function testProductListingWithOrCriteriaTypeIsCreatedFromXml(): void
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
            new SearchCriterionEqual('category', 'accessories'),
            new SearchCriterionEqual('gender', 'male')
        );

        $this->assertEquals($expectedCriteria, $productListing->getCriteria());
    }

    public function testProductListingWithMultiLevelCriteriaIsCreatedFromXml(): void
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
            new SearchCriterionEqual('category', 'accessories'),
            CompositeSearchCriterion::createOr(
                new SearchCriterionGreaterThan('stock_qty', 0),
                new SearchCriterionEqual('backorders', 'true')
            )
        );

        $this->assertEquals($expectedCriteria, $productListing->getCriteria());
    }

    public function testExceptionIsThrownIfUrlKeyAttributeIsMissing(): void
    {
        $this->expectException(MissingUrlKeyXmlAttributeException::class);
        $xml = '<listing />';
        $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriteriaNodeDoesNotExist(): void
    {
        $this->expectException(InvalidNumberOfCriteriaXmlNodesException::class);
        $xml = '<listing url_key="foo"/>';
        $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfTypeAttributeOfListingNodeIsMissing(): void
    {
        $this->expectException(MissingTypeXmlAttributeException::class);
        $xml = '<listing url_key="foo"><criteria/></listing>';
        $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriterionNodeDoesNotHaveAttributeName(): void
    {
        $this->expectException(MissingCriterionAttributeNameXmlAttributeException::class);
        $xml = '<listing url_key="foo"><criteria type="and"><attribute/></criteria></listing>';
        $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriterionNodeDoesNotHaveOperationAttribute(): void
    {
        $this->expectException(MissingCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo"><criteria type="and"><attribute name="bar"/></criteria></listing>';
        $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriterionOperationAttributeIsNotAValidClass(): void
    {
        $this->expectException(InvalidCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo"><criteria type="and"><attribute name="bar" is="baz"/></criteria></listing>';
        $this->productListingBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfNameAttributeIsMissingInProductListingAttributeNode(): void
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

    public function testProductListingWithGivenAttributeIsReturned(): void
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

        $this->assertSame(['foo' => 'bar'], $productListing->getAttributesList()->toArray());
    }

    public function testExceptionIsThrownIfSameAttributeIsSpecifiedMoreThenOnceForTheSameListing(): void
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
