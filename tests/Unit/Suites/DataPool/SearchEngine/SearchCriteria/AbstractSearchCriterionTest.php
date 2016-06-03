<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\Exception\InvalidCriterionNameException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\Exception\InvalidCriterionValueTypeException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
abstract class AbstractSearchCriterionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $fieldKey
     * @param string[] $fieldValues
     * @return SearchDocumentField|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentField($fieldKey, array $fieldValues)
    {
        $stubSearchDocumentField = $this->createMock(SearchDocumentField::class);
        $stubSearchDocumentField->method('getKey')->willReturn($fieldKey);
        $stubSearchDocumentField->method('getValues')->willReturn($fieldValues);

        return $stubSearchDocumentField;
    }

    /**
     * @param SearchDocumentField[] $stubSearchDocumentFields
     * @return SearchDocument|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentWithGivenFields(array $stubSearchDocumentFields)
    {
        $stubSearchDocumentFieldCollection = $this->createMock(SearchDocumentFieldCollection::class);
        $stubSearchDocumentFieldCollection->method('getIterator')
            ->willReturn(new \ArrayIterator($stubSearchDocumentFields));

        $stubSearchDocument = $this->createMock(SearchDocument::class);
        $stubSearchDocument->method('getFieldsCollection')->willReturn($stubSearchDocumentFieldCollection);

        return $stubSearchDocument;
    }

    /**
     * @param string $fieldName
     * @param string $fieldValue
     * @return SearchCriterion
     */
    private function createInstanceOfClassUnderTest($fieldName, $fieldValue)
    {
        $className = SearchCriterion::class . $this->getOperationName();

        if (!class_exists($className)) {
            $this->fail(
                sprintf('Criterion class %s does not exist. Maybe naming convention is not followed?', $className)
            );
        }

        return call_user_func([$className, 'create'], $fieldName, $fieldValue);
    }

    /**
     * @return string
     */
    abstract protected function getOperationName();

    public function testSearchCriteriaInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchCriteria::class, $this->createInstanceOfClassUnderTest('foo', 'bar'));
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->createInstanceOfClassUnderTest('foo', 'bar'));
    }

    public function testExceptionIsThrownIfFieldNameIsNotValid()
    {
        $this->expectException(InvalidCriterionNameException::class);
        $this->createInstanceOfClassUnderTest(1, 'bar');
    }

    public function testExceptionIsThrownIfFieldValueIsNotValid()
    {
        $this->expectException(InvalidCriterionValueTypeException::class);
        $this->createInstanceOfClassUnderTest('foo', []);
    }

    public function testArrayRepresentationOfCriterionIsReturned()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $criterion = $this->createInstanceOfClassUnderTest($fieldName, $fieldValue);

        $result = $criterion->jsonSerialize();
        $expectation = [
            'fieldName'  => $fieldName,
            'fieldValue' => $fieldValue,
            'operation'  => $this->getOperationName()
        ];

        $this->assertSame($expectation, $result);
    }

    public function testFalseIsReturnedIfGivenSearchDocumentContainsNoFieldWithNameMatchingCriterionFieldName()
    {
        $fieldName = 'foo';
        $fieldValues = ['bar'];

        $criterion = $this->createInstanceOfClassUnderTest($fieldName, $fieldValues[0]);

        $stubSearchDocumentField = $this->createStubSearchDocumentField('baz', $fieldValues);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubSearchDocumentField]);

        $this->assertFalse($criterion->matches($stubSearchDocument));
    }

    /**
     * @dataProvider getNonMatchingValues
     * @param string[] $searchDocumentFieldValues
     * @param string $criterionFieldValue
     */
    public function testFalseIsReturnIfGivenSearchDocumentFieldValueIsNotMatchingCriterionValueOnOperation(
        array $searchDocumentFieldValues,
        $criterionFieldValue
    ) {
        $fieldName = 'foo';

        $criterion = $this->createInstanceOfClassUnderTest($fieldName, $criterionFieldValue);

        $stubSearchDocumentField = $this->createStubSearchDocumentField($fieldName, $searchDocumentFieldValues);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubSearchDocumentField]);

        $this->assertFalse($criterion->matches($stubSearchDocument));
    }

    /**
     * @return array[]
     */
    abstract public function getNonMatchingValues();

    /**
     * @dataProvider getMatchingValues
     * @param string $searchDocumentFieldValue
     * @param string $criterionFieldValue
     */
    public function testTrueIsReturnIfGivenSearchDocumentFieldValueMatchesCriterionValueOnOperation(
        $searchDocumentFieldValue,
        $criterionFieldValue
    ) {
        $fieldName = 'foo';

        $criterion = $this->createInstanceOfClassUnderTest($fieldName, $criterionFieldValue);

        $stubSearchDocumentField = $this->createStubSearchDocumentField($fieldName, $searchDocumentFieldValue);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubSearchDocumentField]);

        $this->assertTrue($criterion->matches($stubSearchDocument));
    }

    /**
     * @return array[]
     */
    abstract public function getMatchingValues();
}
