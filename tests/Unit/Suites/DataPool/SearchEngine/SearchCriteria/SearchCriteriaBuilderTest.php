<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class SearchCriteriaBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->builder = new SearchCriteriaBuilder;
    }

    public function testSearchCriterionEqualIsReturned()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $result = $this->builder->create($fieldName, $fieldValue);

        $expectedCriteriaJson = [
            'fieldName' => $fieldName,
            'fieldValue' => $fieldValue,
            'operation' => 'Equal'
        ];

        $this->assertInstanceOf(SearchCriterionEqual::class, $result);
        $this->assertEquals($expectedCriteriaJson, $result->jsonSerialize());
    }

    public function testRangeCriterionIsReturnedIsReturned()
    {
        $fieldName = 'foo';
        $rangeFrom = '0';
        $rangeTo = '1';
        $fieldValue = sprintf('%s%s%s', $rangeFrom, SearchCriteriaBuilder::FILTER_RANGE_DELIMITER, $rangeTo);
        $result = $this->builder->create($fieldName, $fieldValue);

        $expectedCriteriaJson = [
            'condition' => CompositeSearchCriterion::AND_CONDITION,
            'criteria'  => [
                SearchCriterionGreaterOrEqualThan::create($fieldName, $rangeFrom),
                SearchCriterionLessOrEqualThan::create($fieldName, $rangeTo),
            ]
        ];

        $this->assertInstanceOf(CompositeSearchCriterion::class, $result);
        $this->assertEquals($expectedCriteriaJson, $result->jsonSerialize());
    }
}
