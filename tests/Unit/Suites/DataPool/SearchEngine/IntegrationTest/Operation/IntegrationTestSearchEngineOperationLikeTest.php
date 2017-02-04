<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\IntegrationTestSearchEngineOperationLike
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\IntegrationTestSearchEnginePrimitiveOperator
 */
class IntegrationTestSearchEngineOperationLikeTest extends TestCase
{
    private $testFieldName = 'foo';

    private $testFiledValue = 'bar';

    /**
     * @var IntegrationTestSearchEngineOperationLike
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
        $this->operation = new IntegrationTestSearchEngineOperationLike($dataSet);
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

    /**
     * @dataProvider matchingValueProvider
     */
    public function testReturnsTrueIfDocumentFieldValueIsMatching(string $matchingValue)
    {
        $stubSearchDocumentField = $this->createStubSearchDocumentField($this->testFieldName, [$matchingValue]);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields($stubSearchDocumentField);

        $this->assertTrue($this->operation->matches($stubSearchDocument));
    }

    public function testIgnoresDocumentFieldsWithNonStringValues()
    {
        $stubSearchDocumentIntegerField = $this->createStubSearchDocumentField($this->testFieldName, [100]);

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields($stubSearchDocumentIntegerField);

        $this->assertFalse($this->operation->matches($stubSearchDocument));
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
