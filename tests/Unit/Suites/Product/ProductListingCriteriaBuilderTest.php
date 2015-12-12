<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Product\Exception\InvalidCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\InvalidNumberOfCriteriaXmlNodesException;
use LizardsAndPumpkins\Product\Exception\MissingTypeXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingUrlKeyXmlAttributeException;

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
     * @var ProductListingCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var DataVersion
     */
    private $testDataVersion;

    protected function setUp()
    {
        $this->criteriaBuilder = new ProductListingCriteriaBuilder();
        $this->testDataVersion = DataVersion::fromVersionString('-1');
    }

    public function testProductListingCriteriaWithAndTypeIsCreatedFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" website="ru" locale="en_US">
    <criteria type="and">
        <category is="Equal">accessories</category>
        <gender is="Equal">male</gender>
    </criteria>
</listing>
EOX;

        $criteria = $this->criteriaBuilder->createProductListingCriteriaFromXml($xml, $this->testDataVersion);

        $expectedUrlKey = 'men-accessories';
        $expectedContextData = ['version' => '-1', 'website' => 'ru', 'locale' => 'en_US'];
        $expectedCriteria = CompositeSearchCriterion::createAnd(
            SearchCriterionEqual::create('category', 'accessories'),
            SearchCriterionEqual::create('gender', 'male')
        );

        $this->assertInstanceOf(ProductListingCriteria::class, $criteria);
        $this->assertEquals($expectedUrlKey, $criteria->getUrlKey());
        $this->assertEquals($expectedContextData, $criteria->getContextData());
        $this->assertEquals($expectedCriteria, $criteria->getCriteria());
    }

    public function testProductListingCriteriaWithOrTypeIsCreatedFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" website="ru" locale="en_US">
    <criteria type="or">
        <category is="Equal">accessories</category>
        <gender is="Equal">male</gender>
    </criteria>
</listing>
EOX;

        $criteria = $this->criteriaBuilder->createProductListingCriteriaFromXml($xml, $this->testDataVersion);

        $expectedCriteria = CompositeSearchCriterion::createOr(
            SearchCriterionEqual::create('category', 'accessories'),
            SearchCriterionEqual::create('gender', 'male')
        );

        $this->assertEquals($expectedCriteria, $criteria->getCriteria());
    }

    public function testMultiLevelProductListingCriteriaIsCreatedFromXml()
    {
        $xml = <<<EOX
<listing url_key="men-accessories" website="ru" locale="en_US">
    <criteria type="and">
        <category is="Equal">accessories</category>
        <criteria type="or">
            <stock_qty is="GreaterThan">0</stock_qty>
            <backorders is="Equal">true</backorders>
        </criteria>
    </criteria>
</listing>
EOX;

        $criteria = $this->criteriaBuilder->createProductListingCriteriaFromXml($xml, $this->testDataVersion);

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
        $this->criteriaBuilder->createProductListingCriteriaFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriteriaNodeDoesNotExist()
    {
        $this->setExpectedException(InvalidNumberOfCriteriaXmlNodesException::class);
        $xml = '<listing url_key="foo"/>';
        $this->criteriaBuilder->createProductListingCriteriaFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfTypeAttributeOfListingNodeIsMissing()
    {
        $this->setExpectedException(MissingTypeXmlAttributeException::class);
        $xml = '<listing url_key="foo"><criteria/></listing>';
        $this->criteriaBuilder->createProductListingCriteriaFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriterionNodeDoesNotHaveOperationAttribute()
    {
        $this->setExpectedException(MissingCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo"><criteria type="and"><bar/></criteria></listing>';
        $this->criteriaBuilder->createProductListingCriteriaFromXml($xml, $this->testDataVersion);
    }

    public function testExceptionIsThrownIfCriterionOperationAttributeIsNotAValidClass()
    {
        $this->setExpectedException(InvalidCriterionOperationXmlAttributeException::class);
        $xml = '<listing url_key="foo"><criteria type="and"><bar is="baz"/></criteria></listing>';
        $this->criteriaBuilder->createProductListingCriteriaFromXml($xml, $this->testDataVersion);
    }
}
