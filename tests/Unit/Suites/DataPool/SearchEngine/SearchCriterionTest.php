<?php

namespace Brera\DataPool\SearchEngine;

use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField;

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

    public function testFalseIsReturnIfGivenSearchDocumentFieldNameIsNotMatchingCriterionFieldName()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $operation = 'eq';

        $criterion = SearchCriterion::create($fieldName, $fieldValue, $operation);
        $mockSearchDocumentField = $this->createMockSearchDocumentField('baz', $fieldValue);

        $this->assertFalse($criterion->matches($mockSearchDocumentField));
    }

    /**
     * @dataProvider getNonMatchingValues
     * @param string $searchDocumentFieldValue
     * @param string $operation
     * @param string $criterionFieldValue
     */
    public function testFalseIsReturnIfGivenSearchDocumentFieldValueIsNotMatchingCriterionValueOnOperation(
        $searchDocumentFieldValue,
        $operation,
        $criterionFieldValue
    ) {
        $fieldName = 'foo';

        $criterion = SearchCriterion::create($fieldName, $criterionFieldValue, $operation);
        $mockSearchDocumentField = $this->createMockSearchDocumentField($fieldName, $searchDocumentFieldValue);

        $this->assertFalse($criterion->matches($mockSearchDocumentField));
    }

    /**
     * @return array[]
     */
    public function getNonMatchingValues()
    {
        return [
            ['foo', 'eq', 'bar'],
            ['foo', 'neq', 'foo'],
            ['1', 'gt', '1'],
            ['1', 'lt', '1'],
            ['1', 'gte', '2'],
            ['1', 'lte', '0']
        ];
    }

    /**
     * @dataProvider getMatchingValues
     * @param string $searchDocumentFieldValue
     * @param string $operation
     * @param string $criterionFieldValue
     */
    public function testTrueIsReturnIfGivenSearchDocumentFieldValueMatchesCriterionValueOnOperation(
        $searchDocumentFieldValue,
        $operation,
        $criterionFieldValue
    ) {
        $fieldName = 'foo';

        $criterion = SearchCriterion::create($fieldName, $criterionFieldValue, $operation);
        $mockSearchDocumentField = $this->createMockSearchDocumentField($fieldName, $searchDocumentFieldValue);

        $this->assertTrue($criterion->matches($mockSearchDocumentField));
    }

    /**
     * @return array[]
     */
    public function getMatchingValues()
    {
        return [
            ['foo', 'eq', 'foo'],
            ['foo', 'neq', 'bar'],
            ['1', 'gt', '0'],
            ['1', 'lt', '2'],
            ['1', 'gte', '1'],
            ['1', 'lte', '1']
        ];
    }

    /**
     * @param string $fieldKey
     * @param string $fieldValue
     * @return SearchDocumentField|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockSearchDocumentField($fieldKey, $fieldValue)
    {
        $mockSearchDocumentField = $this->getMock(SearchDocumentField::class, [], [], '', false);
        $mockSearchDocumentField->method('getKey')
            ->willReturn($fieldKey);
        $mockSearchDocumentField->method('getValue')
            ->willReturn($fieldValue);

        return $mockSearchDocumentField;
    }
}
