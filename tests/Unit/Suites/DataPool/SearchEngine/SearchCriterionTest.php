<?php

namespace Brera\DataPool\SearchEngine;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchCriterion
 */
class SearchCriterionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Criterion field name should be a string
     */
    public function itShouldFailIfFieldNameIsNotValid()
    {
        SearchCriterion::create(1, 'bar', 'eq');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Criterion field value should be a string
     */
    public function itShouldFailIfFieldValueIsNotValid()
    {
        SearchCriterion::create('foo', 1, 'eq');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid criterion operation
     */
    public function itShouldFailIfOperationIsNotValid()
    {
        SearchCriterion::create('foo', 'bar', 'baz');
    }

    /**
     * @test
     */
    public function itShouldReturnCriterionData()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $operation = 'eq';

        $criterion = SearchCriterion::create($fieldName, $fieldValue, $operation);

        $this->assertEquals($fieldName, $criterion->getFieldName());
        $this->assertEquals($fieldValue, $criterion->getFieldValue());
        $this->assertEquals($operation, $criterion->getOperation());
    }
}
