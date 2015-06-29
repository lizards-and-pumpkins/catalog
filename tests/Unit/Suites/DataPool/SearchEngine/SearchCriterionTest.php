<?php

namespace Brera\DataPool\SearchEngine;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchCriterion
 */
class SearchCriterionTest extends \PHPUnit_Framework_TestCase
{
    public function testJsonSerializableInterfaceIsImplemented()
    {
        $result = SearchCriterion::create('foo', 'bar', 'eq');

        $this->assertInstanceOf(\JsonSerializable::class, $result);
    }

    public function testExceptionIsThrownIfFieldNameIsNotValid()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Criterion field name should be a string');
        SearchCriterion::create(1, 'bar', 'eq');
    }

    public function testExceptionIsThrownIfFieldValueIsNotValid()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Criterion field value should be a string');
        SearchCriterion::create('foo', 1, 'eq');
    }

    public function testExceptionIsThrownIfOperationIsNotValid()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Invalid criterion operation');
        SearchCriterion::create('foo', 'bar', 'baz');
    }

    public function testCriterionDataIsReturned()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $operation = 'eq';

        $criterion = SearchCriterion::create($fieldName, $fieldValue, $operation);

        $this->assertEquals($fieldName, $criterion->getFieldName());
        $this->assertEquals($fieldValue, $criterion->getFieldValue());
        $this->assertEquals($operation, $criterion->getOperation());
    }

    public function testArrayRepresentationOfCriterionIsReturned()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $operation = 'eq';

        $criterion = SearchCriterion::create($fieldName, $fieldValue, $operation);
        $result = $criterion->jsonSerialize();
        $expectation = ['fieldName' => $fieldName, 'fieldValue' => $fieldValue, 'operation' => $operation];

        $this->assertSame($expectation, $result);
    }
}
