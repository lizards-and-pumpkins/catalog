<?php

namespace Brera\DataPool\SearchEngine;

use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchCriterion
 */
class SearchCriterionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $fieldKey
     * @param string $fieldValue
     * @return SearchDocumentField|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentField($fieldKey, $fieldValue)
    {
        $stubSearchDocumentField = $this->getMock(SearchDocumentField::class, [], [], '', false);
        $stubSearchDocumentField->method('getKey')->willReturn($fieldKey);
        $stubSearchDocumentField->method('getValue')->willReturn($fieldValue);

        return $stubSearchDocumentField;
    }

    /**
     * @param SearchDocumentField[] $stubSearchDocumentFields
     * @return SearchDocument|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentWithGivenFields(array $stubSearchDocumentFields)
    {
        $stubSearchDocumentFieldCollection = $this->getMock(SearchDocumentFieldCollection::class, [], [], '', false);
        $stubSearchDocumentFieldCollection->method('getFields')->willReturn($stubSearchDocumentFields);

        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $stubSearchDocument->method('getFieldsCollection')->willReturn($stubSearchDocumentFieldCollection);

        return $stubSearchDocument;
    }

    public function testSearchCriteriaInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchCriteria::class, SearchCriterion::create('foo', 'bar', '='));
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\JsonSerializable::class, SearchCriterion::create('foo', 'bar', '='));
    }

    public function testExceptionIsThrownIfFieldNameIsNotValid()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Criterion field name should be a string');
        SearchCriterion::create(1, 'bar', '=');
    }

    public function testExceptionIsThrownIfFieldValueIsNotValid()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Criterion field value should be a string');
        SearchCriterion::create('foo', 1, '=');
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
        $operation = '=';

        $criterion = SearchCriterion::create($fieldName, $fieldValue, $operation);
        $result = $criterion->jsonSerialize();
        $expectation = ['fieldName' => $fieldName, 'fieldValue' => $fieldValue, 'operation' => $operation];

        $this->assertSame($expectation, $result);
    }

    public function testFalseIsReturnedIfGivenSearchDocumentContainsNoFieldWithNametMatchingCriterionFieldName()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $operation = '=';

        $criterion = SearchCriterion::create($fieldName, $fieldValue, $operation);

        $stubSearchDocumentField = $this->createStubSearchDocumentField('baz', $fieldValue);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubSearchDocumentField]);

        $this->assertFalse($criterion->matches($stubSearchDocument));
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

        $stubSearchDocumentField = $this->createStubSearchDocumentField($fieldName, $searchDocumentFieldValue);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubSearchDocumentField]);

        $this->assertFalse($criterion->matches($stubSearchDocument));
    }

    /**
     * @return array[]
     */
    public function getNonMatchingValues()
    {
        return [
            ['foo', '=', 'bar'],
            ['foo', '!=', 'foo'],
            ['1', '>', '1'],
            ['1', '<', '1'],
            ['1', '>=', '2'],
            ['1', '<=', '0']
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

        $stubSearchDocumentField = $this->createStubSearchDocumentField($fieldName, $searchDocumentFieldValue);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubSearchDocumentField]);

        $this->assertTrue($criterion->matches($stubSearchDocument));
    }

    /**
     * @return array[]
     */
    public function getMatchingValues()
    {
        return [
            ['foo', '=', 'foo'],
            ['foo', '!=', 'bar'],
            ['1', '>', '0'],
            ['1', '<', '2'],
            ['1', '>=', '1'],
            ['1', '<=', '1']
        ];
    }
}
