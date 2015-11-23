<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformation;
use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformationRegistry;
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

    protected function setUp()
    {
        $this->stubFacetFieldTransformationRegistry = $this->getMock(FacetFieldTransformationRegistry::class);
        $this->builder = new SearchCriteriaBuilder($this->stubFacetFieldTransformationRegistry);
    }

    public function testSearchCriterionEqualIsReturned()
    {
        $parameterName = 'foo';
        $parameterValue = 'bar';
        $result = $this->builder->fromFieldNameAndValue($parameterName, $parameterValue);

        $expectedCriteriaJson = [
            'fieldName' => $parameterName,
            'fieldValue' => $parameterValue,
            'operation' => 'Equal'
        ];

        $this->assertInstanceOf(SearchCriterionEqual::class, $result);
        $this->assertEquals($expectedCriteriaJson, $result->jsonSerialize());
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

        /** @var CompositeSearchCriterion $result */
        $result = $this->builder->fromFieldNameAndValue($parameterName, $parameterValue);

        $expectedCriteriaJson = [
            'condition' => CompositeSearchCriterion::AND_CONDITION,
            'criteria'  => [
                SearchCriterionGreaterOrEqualThan::create($parameterName, $rangeFrom),
                SearchCriterionLessOrEqualThan::create($parameterName, $rangeTo),
            ]
        ];

        $this->assertInstanceOf(CompositeSearchCriterion::class, $result);
        $this->assertEquals($expectedCriteriaJson, $result->jsonSerialize());
    }

    public function testCompositeCriteriaWithListOfFieldsMatchingSameStringWithOrConditionIsReturned()
    {
        $fields = ['foo', 'bar'];
        $queryString = 'baz';
        $result = $this->builder->createCriteriaForAnyOfGivenFieldsContainsString($fields, $queryString);

        $expectedCriteriaJson = [
            'condition' => CompositeSearchCriterion::OR_CONDITION,
            'criteria'  => array_map(function ($fieldName) use ($queryString) {
                return SearchCriterionLike::create($fieldName, $queryString);
            }, $fields)
        ];

        $this->assertInstanceOf(CompositeSearchCriterion::class, $result);
        $this->assertEquals($expectedCriteriaJson, $result->jsonSerialize());
    }
}
