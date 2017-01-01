<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation;

use LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\Exception\InvalidSearchEngineOperationDataSetException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\IntegrationTestSearchEngineOperationGreaterThan
 */
class IntegrationTestSearchEngineOperationGreaterThanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $fieldKey
     * @param string[] $fieldValues
     * @return SearchDocumentField|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentField(string $fieldKey, array $fieldValues) : SearchDocumentField
    {
        $stubSearchDocumentField = $this->createMock(SearchDocumentField::class);
        $stubSearchDocumentField->method('getKey')->willReturn($fieldKey);
        $stubSearchDocumentField->method('getValues')->willReturn($fieldValues);

        return $stubSearchDocumentField;
    }

    /**
     * @param SearchDocumentField[] ...$stubSearchDocumentFields
     * @return SearchDocument|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentWithGivenFields(
        SearchDocumentField ...$stubSearchDocumentFields
    ) : SearchDocument {
        $stubSearchDocumentFieldCollection = $this->createMock(SearchDocumentFieldCollection::class);
        $stubSearchDocumentFieldCollection->method('getIterator')
            ->willReturn(new \ArrayIterator($stubSearchDocumentFields));

        $stubSearchDocument = $this->createMock(SearchDocument::class);
        $stubSearchDocument->method('getFieldsCollection')->willReturn($stubSearchDocumentFieldCollection);

        return $stubSearchDocument;
    }

    public function testImplementsIntegrationTestSearchEngineOperationInterface()
    {
        $dataSet = ['fieldName' => 'foo', 'fieldValue' => 'bar'];
        $operation = new IntegrationTestSearchEngineOperationGreaterThan($dataSet);

        $this->assertInstanceOf(IntegrationTestSearchEngineOperation::class, $operation);
    }

    public function testThrowsAnExceptionIfSearchEngineOperationDataArrayDoesNotContainFieldName()
    {
        $this->expectException(InvalidSearchEngineOperationDataSetException::class);
        $this->expectExceptionMessage('Search engine operation data set array does not contain "fieldName" element.');

        new IntegrationTestSearchEngineOperationGreaterThan(['fieldValue' => 'bar']);
    }

    public function testThrowsAnExceptionIfSearchEngineOperationFieldNameIsNonString()
    {
        $this->expectException(InvalidSearchEngineOperationDataSetException::class);
        $this->expectExceptionMessage('Search engine operation field name must be a string.');

        new IntegrationTestSearchEngineOperationGreaterThan(['fieldName' => true, 'fieldValue' => 'bar']);
    }

    /**
     * @dataProvider emptyStringProvider
     */
    public function testThrowsAnExceptionIfSearchEngineOperationFieldNameIsEmpty(string $emptyString)
    {
        $this->expectException(InvalidSearchEngineOperationDataSetException::class);
        $this->expectExceptionMessage('Search engine operation field name must not be empty.');

        new IntegrationTestSearchEngineOperationGreaterThan(['fieldName' => $emptyString, 'fieldValue' => 'bar']);
    }

    public function testThrowsAnExceptionIfSearchEngineOperationDataArrayDoesNotContainFieldValue()
    {
        $this->expectException(InvalidSearchEngineOperationDataSetException::class);
        $this->expectExceptionMessage('Search engine operation data set array does not contain "fieldValue" element.');

        new IntegrationTestSearchEngineOperationGreaterThan(['fieldName' => 'foo']);
    }

    public function testReturnsFalseIfDocumentHaveNoFieldInvolvedIntoAnOperation()
    {
        $dataSet = ['fieldName' => 'foo', 'fieldValue' => 'bar'];
        $operation = new IntegrationTestSearchEngineOperationGreaterThan($dataSet);

        $stubSearchDocumentField = $this->createStubSearchDocumentField('baz', ['qux']);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields($stubSearchDocumentField);

        $this->assertFalse($operation->matches($stubSearchDocument));
    }

    /**
     * @dataProvider nonMatchingValuesProvider
     * @param mixed $operationFiledValue
     * @param mixed $documentFieldValue
     */
    public function testReturnsFalseIfDocumentFieldValueIsNotMatching($operationFiledValue, $documentFieldValue)
    {
        $testFieldName = 'foo';

        $dataSet = ['fieldName' => $testFieldName, 'fieldValue' => $operationFiledValue];
        $operation = new IntegrationTestSearchEngineOperationGreaterThan($dataSet);

        $stubSearchDocumentField = $this->createStubSearchDocumentField($testFieldName, [$documentFieldValue]);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields($stubSearchDocumentField);

        $this->assertFalse($operation->matches($stubSearchDocument));
    }

    /**
     * @dataProvider matchingValuesProvider
     * @param mixed $operationFiledValue
     * @param mixed $documentFieldValue
     */
    public function testReturnsTrueIfDocumentFieldValueIsMatching($operationFiledValue, $documentFieldValue)
    {
        $testFieldName = 'foo';

        $dataSet = ['fieldName' => $testFieldName, 'fieldValue' => $operationFiledValue];
        $operation = new IntegrationTestSearchEngineOperationGreaterThan($dataSet);

        $stubSearchDocumentField = $this->createStubSearchDocumentField($testFieldName, [$documentFieldValue]);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields($stubSearchDocumentField);

        $this->assertTrue($operation->matches($stubSearchDocument));
    }

    /**
     * @return array[]
     */
    public function emptyStringProvider() : array
    {
        return [
            [''],
            [' '],
        ];
    }

    /**
     * @return array[]
     */
    public function nonMatchingValuesProvider() : array
    {
        return [
            [1, 0],
            ['b', 'a'],
            [2, '1'],
        ];
    }

    /**
     * @return array[]
     */
    public function matchingValuesProvider() : array
    {
        return [
            [0, 1],
            ['a', 'b'],
            [1, '2'],
        ];
    }
}
