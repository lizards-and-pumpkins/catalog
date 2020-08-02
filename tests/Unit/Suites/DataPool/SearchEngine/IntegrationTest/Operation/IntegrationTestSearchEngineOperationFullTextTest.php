<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation;

use LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\Exception\InvalidSearchEngineOperationDataSetException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\IntegrationTestSearchEngineOperationFullText
 */
class IntegrationTestSearchEngineOperationFullTextTest extends TestCase
{
    private $testFiledValue = 'bar';

    /**
     * @var IntegrationTestSearchEngineOperationFullText
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
        $dataSet = ['fieldValue' => $this->testFiledValue];
        $this->operation = new IntegrationTestSearchEngineOperationFullText($dataSet);
    }

    public function testImplementsIntegrationTestSearchEngineOperationInterface(): void
    {
        $this->assertInstanceOf(IntegrationTestSearchEngineOperation::class, $this->operation);
    }

    public function testThrowsAnExceptionIfSearchEngineOperationDataArrayDoesNotContainFieldValue(): void
    {
        $this->expectException(InvalidSearchEngineOperationDataSetException::class);
        $this->expectExceptionMessage('Search engine operation data set array does not contain "fieldValue" element.');

        new IntegrationTestSearchEngineOperationFullText([]);
    }

    public function testThrowsAnExceptionIfSearchEngineOperationFieldValueIsNonString(): void
    {
        $this->expectException(InvalidSearchEngineOperationDataSetException::class);
        $this->expectExceptionMessage('Search engine operation field value must be a string.');

        new IntegrationTestSearchEngineOperationFullText(['fieldValue' => 1]);
    }

    public function testReturnsFalseIfDocumentHaveNoFields(): void
    {
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields();
        $this->assertFalse($this->operation->matches($stubSearchDocument));
    }

    public function testReturnsFalseIfDocumentFieldValueIsNotMatching(): void
    {
        $stubSearchDocumentField = $this->createStubSearchDocumentField('foo', ['qux']);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields($stubSearchDocumentField);

        $this->assertFalse($this->operation->matches($stubSearchDocument));
    }

    /**
     * @dataProvider matchingValueProvider
     */
    public function testReturnsTrueIfDocumentFieldValueIsMatching(string $matchingValue): void
    {
        $stubSearchDocumentField = $this->createStubSearchDocumentField('foo', [$matchingValue]);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields($stubSearchDocumentField);

        $this->assertTrue($this->operation->matches($stubSearchDocument));
    }

    public function testIgnoresDocumentFieldsWithNonStringValues(): void
    {
        $stubSearchDocumentIntegerField = $this->createStubSearchDocumentField('bar', [100]);
        $stubSearchDocumentStringField = $this->createStubSearchDocumentField('foo', [$this->testFiledValue]);

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields(
            $stubSearchDocumentIntegerField,
            $stubSearchDocumentStringField
        );

        $this->assertTrue($this->operation->matches($stubSearchDocument));
    }

    /**
     * @return array[]
     */
    public function matchingValueProvider() : array
    {
        return [
            [$this->testFiledValue],
            ['Some text surrounding "' . $this->testFiledValue . '".'],
        ];
    }
}
