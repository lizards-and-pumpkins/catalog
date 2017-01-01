<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformation;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterOrEqualThan
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessOrEqualThan
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLike
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
        $this->stubFacetFieldTransformationRegistry = $this->createMock(FacetFieldTransformationRegistry::class);
        $this->stubGlobalProductListingCriteria = $this->createMock(SearchCriteria::class);
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
        $expectedCriteria = new SearchCriterionEqual($parameterName, $parameterValue);

        $this->assertEquals($expectedCriteria, $result);
    }

    public function testRangeCriterionIsReturned()
    {
        $parameterName = 'foo';
        $rangeFrom = '0';
        $rangeTo = '1';
        $parameterValue = 'whatever';

        $stubFacetFieldRange = $this->createMock(FacetFilterRange::class);
        $stubFacetFieldRange->method('from')->willReturn($rangeFrom);
        $stubFacetFieldRange->method('to')->willReturn($rangeTo);

        $stubFacetFieldTransformation = $this->createMock(FacetFieldTransformation::class);
        $stubFacetFieldTransformation->method('decode')->willReturn($stubFacetFieldRange);

        $this->stubFacetFieldTransformationRegistry->method('hasTransformationForCode')->willReturn(true);
        $this->stubFacetFieldTransformationRegistry->method('getTransformationByCode')
            ->willReturn($stubFacetFieldTransformation);

        $result = $this->builder->fromFieldNameAndValue($parameterName, $parameterValue);

        $expectedCriteria = CompositeSearchCriterion::createAnd(
            new SearchCriterionGreaterOrEqualThan($parameterName, $rangeFrom),
            new SearchCriterionLessOrEqualThan($parameterName, $rangeTo)
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
                new SearchCriterionLike('foo', $queryString),
                new SearchCriterionLike('bar', $queryString)
            ),
            $this->stubGlobalProductListingCriteria
        );

        $this->assertEquals($expectedCriteria, $result);
    }
}
