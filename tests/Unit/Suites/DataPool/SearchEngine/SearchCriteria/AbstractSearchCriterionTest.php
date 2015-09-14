<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

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
        $stubSearchDocumentFieldCollection->method('getIterator')
            ->willReturn(new \ArrayIterator($stubSearchDocumentFields));

        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
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
        $this->setExpectedException(\InvalidArgumentException::class, 'Criterion field name should be a string');
        $this->createInstanceOfClassUnderTest(1, 'bar');
    }

    public function testExceptionIsThrownIfFieldValueIsNotValid()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Criterion field value should be a string');
        $this->createInstanceOfClassUnderTest('foo', 1);
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
        $fieldValue = 'bar';

        $criterion = $this->createInstanceOfClassUnderTest($fieldName, $fieldValue);

        $stubSearchDocumentField = $this->createStubSearchDocumentField('baz', $fieldValue);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubSearchDocumentField]);

        $this->assertFalse($criterion->matches($stubSearchDocument));
    }

    /**
     * @dataProvider getNonMatchingValues
     * @param string $searchDocumentFieldValue
     * @param string $criterionFieldValue
     */
    public function testFalseIsReturnIfGivenSearchDocumentFieldValueIsNotMatchingCriterionValueOnOperation(
        $searchDocumentFieldValue,
        $criterionFieldValue
    ) {
        $fieldName = 'foo';

        $criterion = $this->createInstanceOfClassUnderTest($fieldName, $criterionFieldValue);

        $stubSearchDocumentField = $this->createStubSearchDocumentField($fieldName, $searchDocumentFieldValue);
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
