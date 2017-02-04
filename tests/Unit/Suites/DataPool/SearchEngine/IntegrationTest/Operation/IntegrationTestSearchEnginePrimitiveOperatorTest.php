<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation;

use LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\Exception\InvalidSearchEngineOperationDataSetException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\IntegrationTestSearchEnginePrimitiveOperator
 */
class IntegrationTestSearchEnginePrimitiveOperatorTest extends TestCase
{
    private $testFieldName = 'foo';

    private $testFiledValue = 'bar';

    /**
     * @var IntegrationTestSearchEnginePrimitiveOperator
     */
    private $operator;

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

    final protected function setUp()
    {
        $dataSet = ['fieldName' => $this->testFieldName, 'fieldValue' => $this->testFiledValue];
        $this->operator = new IntegrationTestSearchEnginePrimitiveOperator($dataSet);
    }

    public function testThrowsAnExceptionIfSearchEngineOperationDataArrayDoesNotContainFieldName()
    {
        $this->expectException(InvalidSearchEngineOperationDataSetException::class);
        $this->expectExceptionMessage('Search engine operation data set array does not contain "fieldName" element.');

        new IntegrationTestSearchEnginePrimitiveOperator(['fieldValue' => 'bar']);
    }

    public function testThrowsAnExceptionIfSearchEngineOperationFieldNameIsNonString()
    {
        $this->expectException(InvalidSearchEngineOperationDataSetException::class);
        $this->expectExceptionMessage('Search engine operation field name must be a string.');

        new IntegrationTestSearchEnginePrimitiveOperator(['fieldName' => true, 'fieldValue' => 'bar']);
    }

    /**
     * @dataProvider emptyStringProvider
     */
    public function testThrowsAnExceptionIfSearchEngineOperationFieldNameIsEmpty(string $emptyString)
    {
        $this->expectException(InvalidSearchEngineOperationDataSetException::class);
        $this->expectExceptionMessage('Search engine operation field name must not be empty.');

        new IntegrationTestSearchEnginePrimitiveOperator(['fieldName' => $emptyString, 'fieldValue' => 'bar']);
    }

    public function testThrowsAnExceptionIfSearchEngineOperationDataArrayDoesNotContainFieldValue()
    {
        $this->expectException(InvalidSearchEngineOperationDataSetException::class);
        $this->expectExceptionMessage('Search engine operation data set array does not contain "fieldValue" element.');

        new IntegrationTestSearchEnginePrimitiveOperator(['fieldName' => 'foo']);
    }

    public function testReturnsFalseIfDocumentHaveNoFieldInvolvedIntoAnOperation()
    {
        $stubSearchDocumentField = $this->createStubSearchDocumentField('baz', ['qux']);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields($stubSearchDocumentField);

        $matchesClosure = function () {

        };

        $this->assertFalse($this->operator->matches($stubSearchDocument, $matchesClosure));
    }

    public function testReturnsFalseIfDocumentFieldValueIsNotMatching()
    {
        $stubSearchDocumentField = $this->createStubSearchDocumentField($this->testFieldName, ['qux']);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields($stubSearchDocumentField);

        $matchesClosure = function () {
            return false;
        };

        $this->assertFalse($this->operator->matches($stubSearchDocument, $matchesClosure));
    }

    public function testReturnsTrueIfDocumentFieldValueIsMatching()
    {
        $stubSearchDocumentField = $this->createStubSearchDocumentField($this->testFieldName, [$this->testFiledValue]);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields($stubSearchDocumentField);

        $matchesClosure = function () {
            return true;
        };

        $this->assertTrue($this->operator->matches($stubSearchDocument, $matchesClosure));
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
}
