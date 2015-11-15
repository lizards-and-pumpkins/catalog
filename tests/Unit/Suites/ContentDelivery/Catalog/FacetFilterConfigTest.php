<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformationCollection;
use LizardsAndPumpkins\Product\AttributeCode;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\FacetFilterConfig
 * @uses   \LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformationCollection
 */
class FacetFilterConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubAttributeCode;

    /**
     * @var FacetFieldRangeCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFacetFieldRangeCollection;

    /**
     * @var FacetFieldTransformationCollection
     */
    private $stubFacetFieldTransformationCollection;

    /**
     * @var FacetFilterConfig
     */
    private $facetFilterConfig;

    protected function setUp()
    {
        $this->stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $this->stubFacetFieldRangeCollection = $this->getMock(FacetFieldRangeCollection::class, [], [], '', false);
        $this->stubFacetFieldTransformationCollection = $this->getMock(FacetFieldTransformationCollection::class);
        $this->facetFilterConfig = new FacetFilterConfig(
            $this->stubAttributeCode,
            $this->stubFacetFieldRangeCollection,
            $this->stubFacetFieldTransformationCollection
        );
    }

    public function testAttributeCodeIsReturned()
    {
        $this->assertSame($this->stubAttributeCode, $this->facetFilterConfig->getAttributeCode());
    }

    public function testFacetFieldRangeCollectionIsReturned()
    {
        $this->assertSame($this->stubFacetFieldRangeCollection, $this->facetFilterConfig->getRangeCollection());
    }

    public function testFacetFilterTransformationsCollectionIsReturned()
    {
        $this->assertSame(
            $this->stubFacetFieldTransformationCollection,
            $this->facetFilterConfig->getTransformations()
        );
    }
}
