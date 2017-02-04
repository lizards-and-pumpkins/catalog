<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\IntegrationTestSearchEngineOperationEqual
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\IntegrationTestSearchEnginePrimitiveOperator
 */
class IntegrationTestSearchEngineOperationEqualTest extends TestCase
{
    private $testFieldName = 'foo';

    private $testFiledValue = 'bar';

    /**
     * @var IntegrationTestSearchEngineOperationEqual
     */
    private $operation;

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
        $this->operation = new IntegrationTestSearchEngineOperationEqual($dataSet);
    }

    public function testImplementsIntegrationTestSearchEngineOperationInterface()
    {
        $this->assertInstanceOf(IntegrationTestSearchEngineOperation::class, $this->operation);
    }

    public function testReturnsFalseIfDocumentFieldValueIsNotMatching()
    {
        $stubSearchDocumentField = $this->createStubSearchDocumentField($this->testFieldName, ['qux']);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields($stubSearchDocumentField);

        $this->assertFalse($this->operation->matches($stubSearchDocument));
    }

    public function testReturnsTrueIfDocumentFieldValueIsMatching()
    {
        $stubSearchDocumentField = $this->createStubSearchDocumentField($this->testFieldName, [$this->testFiledValue]);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields($stubSearchDocumentField);

        $this->assertTrue($this->operation->matches($stubSearchDocument));
    }
}
