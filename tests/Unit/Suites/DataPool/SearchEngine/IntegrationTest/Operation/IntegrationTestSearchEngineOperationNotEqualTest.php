<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation;

use LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\Exception\InvalidSearchEngineOperationDataSetException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\IntegrationTestSearchEngineOperationNotEqual
 */
class IntegrationTestSearchEngineOperationNotEqualTest extends TestCase
{
    private $testFieldName = 'foo';

    private $testFiledValue = 'bar';

    /**
     * @var IntegrationTestSearchEngineOperationNotEqual
     */
    private $operation;

    /**
     * @param string $fieldKey
     * @param string[] $fieldValues
     * @return SearchDocumentField|MockObject
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
     * @return SearchDocument|MockObject
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

    final protected function setUp(): void
    {
        $dataSet = ['fieldName' => $this->testFieldName, 'fieldValue' => $this->testFiledValue];
        $this->operation = new IntegrationTestSearchEngineOperationNotEqual($dataSet);
    }

    public function testImplementsIntegrationTestSearchEngineOperationInterface(): void
    {
        $this->assertInstanceOf(IntegrationTestSearchEngineOperation::class, $this->operation);
    }

    public function testThrowsAnExceptionIfSearchEngineOperationDataArrayDoesNotContainFieldName(): void
    {
        $this->expectException(InvalidSearchEngineOperationDataSetException::class);
        $this->expectExceptionMessage('Search engine operation data set array does not contain "fieldName" element.');

        new IntegrationTestSearchEngineOperationNotEqual(['fieldValue' => 'bar']);
    }

    public function testThrowsAnExceptionIfSearchEngineOperationFieldNameIsNonString(): void
    {
        $this->expectException(InvalidSearchEngineOperationDataSetException::class);
        $this->expectExceptionMessage('Search engine operation field name must be a string.');

        new IntegrationTestSearchEngineOperationNotEqual(['fieldName' => true, 'fieldValue' => 'bar']);
    }

    /**
     * @dataProvider emptyStringProvider
     */
    public function testThrowsAnExceptionIfSearchEngineOperationFieldNameIsEmpty(string $emptyString): void
    {
        $this->expectException(InvalidSearchEngineOperationDataSetException::class);
        $this->expectExceptionMessage('Search engine operation field name must not be empty.');

        new IntegrationTestSearchEngineOperationNotEqual(['fieldName' => $emptyString, 'fieldValue' => 'bar']);
    }

    public function testThrowsAnExceptionIfSearchEngineOperationDataArrayDoesNotContainFieldValue(): void
    {
        $this->expectException(InvalidSearchEngineOperationDataSetException::class);
        $this->expectExceptionMessage('Search engine operation data set array does not contain "fieldValue" element.');

        new IntegrationTestSearchEngineOperationNotEqual(['fieldName' => 'foo']);
    }

    public function testReturnsTrueIfDocumentHasNoFieldInvolvedIntoAnOperation(): void
    {
        $stubSearchDocumentField = $this->createStubSearchDocumentField('baz', ['qux']);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields($stubSearchDocumentField);

        $this->assertTrue($this->operation->matches($stubSearchDocument));
    }

    public function testReturnsFalseIfDocumentFieldValueIsMatching(): void
    {
        $stubSearchDocumentField = $this->createStubSearchDocumentField($this->testFieldName, [$this->testFiledValue]);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields($stubSearchDocumentField);

        $this->assertFalse($this->operation->matches($stubSearchDocument));
    }

    public function testReturnsTrueIfDocumentFieldValueIsNotMatching(): void
    {
        $stubSearchDocumentField = $this->createStubSearchDocumentField($this->testFieldName, ['qux']);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields($stubSearchDocumentField);

        $this->assertTrue($this->operation->matches($stubSearchDocument));
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
