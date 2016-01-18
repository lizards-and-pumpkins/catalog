<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation\FacetFieldTransformation;
use LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class SearchCriteriaBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FacetFieldTransformationRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFacetFieldTransformationRegistry;

    /**
     * @var SearchCriteriaBuilder
     */
    private $builder;

    /**
     * @var SearchCriteria
     */
    private $stubGlobalProductListingCriteria;

    protected function setUp()
    {
        $this->stubFacetFieldTransformationRegistry = $this->getMock(FacetFieldTransformationRegistry::class);
        $this->stubGlobalProductListingCriteria = $this->getMock(SearchCriteria::class);
        $this->builder = new SearchCriteriaBuilder(
            $this->stubFacetFieldTransformationRegistry,
            $this->stubGlobalProductListingCriteria
        );
    }

    public function testSearchCriterionEqualIsReturned()
    {
        $parameterName = 'foo';
        $parameterValue = 'bar';

        $result = $this->builder->fromFieldNameAndValue($parameterName, $parameterValue);
        $expectedCriteria = SearchCriterionEqual::create($parameterName, $parameterValue);

        $this->assertEquals($expectedCriteria, $result);
    }

    public function testRangeCriterionIsReturned()
    {
        $parameterName = 'foo';
        $rangeFrom = '0';
        $rangeTo = '1';
        $parameterValue = 'whatever';

        $stubFacetFieldRange = $this->getMock(FacetFilterRange::class, [], [], '', false);
        $stubFacetFieldRange->method('from')->willReturn($rangeFrom);
        $stubFacetFieldRange->method('to')->willReturn($rangeTo);

        $stubFacetFieldTransformation = $this->getMock(FacetFieldTransformation::class);
        $stubFacetFieldTransformation->method('decode')->willReturn($stubFacetFieldRange);

        $this->stubFacetFieldTransformationRegistry->method('hasTransformationForCode')->willReturn(true);
        $this->stubFacetFieldTransformationRegistry->method('getTransformationByCode')
            ->willReturn($stubFacetFieldTransformation);

        $result = $this->builder->fromFieldNameAndValue($parameterName, $parameterValue);

        $expectedCriteria = CompositeSearchCriterion::createAnd(
            SearchCriterionGreaterOrEqualThan::create($parameterName, $rangeFrom),
            SearchCriterionLessOrEqualThan::create($parameterName, $rangeTo)
        );

        $this->assertEquals($expectedCriteria, $result);
    }

    public function testCompositeCriteriaWithListOfFieldsMatchingSameStringWithOrConditionIsReturned()
    {
        $fields = ['foo', 'bar'];
        $queryString = 'baz';

        $result = $this->builder->createCriteriaForAnyOfGivenFieldsContainsString($fields, $queryString);

        $expectedCriteria = CompositeSearchCriterion::createAnd(
            CompositeSearchCriterion::createOr(
                SearchCriterionLike::create('foo', $queryString),
                SearchCriterionLike::create('bar', $queryString)
            ),
            $this->stubGlobalProductListingCriteria
        );

        $this->assertEquals($expectedCriteria, $result);
    }
}
