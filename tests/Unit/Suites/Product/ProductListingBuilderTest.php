<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Product\Exception\InvalidCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\InvalidNumberOfCriteriaXmlNodesException;
use LizardsAndPumpkins\Product\Exception\MissingCriterionAttributeNameXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingTypeXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingUrlKeyXmlAttributeException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\Product\ProductListing
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
 * @uses   \LizardsAndPumpkins\UrlKey
 * @uses   \LizardsAndPumpkins\DataVersion
 */
class ProductListingBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingBuilder
     */
    private $criteriaBuilder;

    /**
     * @var DataVersion
     */
    private $testDataVersion;

    protected function setUp()
    {
        $this->criteriaBuilder = new ProductListingBuilder();
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

        $criteria = $this->criteriaBuilder->createProductListingFromXml($xml, $this->testDataVersion);

        $expectedUrlKey = 'men-accessories';
        $expectedContextData = ['version' => '-1', 'website' => 'ru', 'locale' => 'en_US'];
        $expectedCriteria = CompositeSearchCriterion::createAnd(
            SearchCriterionEqual::create('category', 'accessories'),
            SearchCriterionEqual::create('gender', 'male')
        );

        $this->assertInstanceOf(ProductListing::class, $criteria);
        $this->assertEquals($expectedUrlKey, $criteria->getUrlKey());
        $this->assertEquals($expectedContextData, $criteria->getContextData());
        $this->assertEquals($expectedCriteria, $criteria->getCriteria());
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

        $criteria = $this->criteriaBuilder->createProductListingFromXml($xml, $this->testDataVersion);

        $expectedCriteria = CompositeSearchCriterion::createOr(
            SearchCriterionEqual::create('category', 'accessories'),
            SearchCriterionEqual::create('gender', 'male')
        );

        $this->assertEquals($expectedCriteria, $criteria->getCriteria());
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

        $criteria = $this->criteriaBuilder->createProductListingFromXml($xml, $this->testDataVersion);

        $expectedCriteria = CompositeSearchCriterion::createAnd(
            SearchCriterionEqual::create('category', 'accessories'),
            CompositeSearchCriterion::createOr(
                SearchCriterionGreaterThan::create('stock_qty', 0),
                SearchCriterionEqual::create('backorders', 'true')
            )
        );

        $this->assertEquals($expectedCriteria, $criteria->getCriteria());
    }

    public function testExceptionIsThrownIfUrlKeyAttributeIsMissing()
    {
        $this->setExpectedException(MissingUrlKeyXmlAttributeException::class);
        $xml = '<listing />';
        $this->criteriaBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriteriaNodeDoesNotExist()
    {
        $this->setExpectedException(InvalidNumberOfCriteriaXmlNodesException::class);
        $xml = '<listing url_key="foo"/>';
        $this->criteriaBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfTypeAttributeOfListingNodeIsMissing()
    {
        $this->setExpectedException(MissingTypeXmlAttributeException::class);
        $xml = '<listing url_key="foo"><criteria/></listing>';
        $this->criteriaBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriterionNodeDoesNotHaveAttributeName()
    {
        $this->setExpectedException(MissingCriterionAttributeNameXmlAttributeException::class);
        $xml = '<listing url_key="foo"><criteria type="and"><attribute/></criteria></listing>';
        $this->criteriaBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriterionNodeDoesNotHaveOperationAttribute()
    {
        $this->setExpectedException(MissingCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo"><criteria type="and"><attribute name="bar"/></criteria></listing>';
        $this->criteriaBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriterionOperationAttributeIsNotAValidClass()
    {
        $this->setExpectedException(InvalidCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo"><criteria type="and"><attribute name="bar" is="baz"/></criteria></listing>';
        $this->criteriaBuilder->createProductListingFromXml($xml, $this->testDataVersion);
    }
}
