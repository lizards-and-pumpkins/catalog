<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

use Brera\Context\Context;
use Brera\Context\VersionedContext;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;
use Brera\DataVersion;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \Brera\DataVersion
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\Context\ContextBuilder
 */
class SearchDocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchDocumentFieldCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDocumentFieldsCollection;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $testContext;

    /**
     * @var string
     */
    private $content = 'foo';

    /**
     * @var SearchDocument
     */
    private $searchDocument;

    protected function setUp()
    {
        $this->mockDocumentFieldsCollection = $this->getMock(SearchDocumentFieldCollection::class, [], [], '', false);
        $this->testContext = new VersionedContext(DataVersion::fromVersionString('123'));

        $this->searchDocument = new SearchDocument(
            $this->mockDocumentFieldsCollection,
            $this->testContext,
            $this->content
        );
    }

    public function testSearchDocumentIsCreated()
    {
        $this->assertSame($this->mockDocumentFieldsCollection, $this->searchDocument->getFieldsCollection());
        $this->assertSame($this->testContext, $this->searchDocument->getContext());
        $this->assertSame($this->content, $this->searchDocument->getContent());
    }

    public function testFalseIsReturnedIfInputArrayIsEmpty()
    {
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, []);

        $this->assertFalse($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    /**
     * @test
     */
    public function itShouldReturnFalseSearchDocumentDoesNotContainAFieldWithMatchingName()
    {
        $dummyFieldValue = 'field-name';

        $mockCriterion = $this->createMockCriterion('field-name', $dummyFieldValue, 'eq');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$mockCriterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField('non-matching-field-name', $dummyFieldValue);

        $this->mockDocumentFieldsCollection->expects($this->any())
            ->method('getFields')
            ->willReturn([$mockSearchDocumentField]);

        $this->assertFalse($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    /**
     * @test
     */
    public function itShouldReturnFalseSearchDocumentDoesNotContainAFieldWithValueEqualsToGivenValue()
    {
        $dummyFieldName = 'field-name';

        $mockCriterion = $this->createMockCriterion($dummyFieldName, 'field-value', 'eq');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$mockCriterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField($dummyFieldName, 'non-matching-field-value');

        $this->mockDocumentFieldsCollection->expects($this->any())
            ->method('getFields')
            ->willReturn([$mockSearchDocumentField]);

        $this->assertFalse($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    /**
     * @test
     */
    public function itShouldReturnTrueIfSearchDocumentContainsAFieldWithValueEqualsToGivenValue()
    {
        $dummyFieldName = 'field-name';
        $dummyFieldValue = 'field-value';

        $mockCriterion = $this->createMockCriterion($dummyFieldName, $dummyFieldValue, 'eq');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$mockCriterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField($dummyFieldName, $dummyFieldValue);

        $this->mockDocumentFieldsCollection->expects($this->once())
            ->method('getFields')
            ->willReturn([$mockSearchDocumentField]);

        $this->assertTrue($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    /**
     * @test
     */
    public function itShouldReturnTrueIfSearchDocumentContainsAFieldWithValueNotEqualToGivenValue()
    {
        $dummyFieldName = 'field-name';

        $mockCriterion = $this->createMockCriterion($dummyFieldName, 'field-value', 'neq');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$mockCriterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField($dummyFieldName, 'non-matching-field-value');

        $this->mockDocumentFieldsCollection->expects($this->once())
            ->method('getFields')
            ->willReturn([$mockSearchDocumentField]);

        $this->assertTrue($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    /**
     * @test
     */
    public function itShouldReturnTrueIfSearchDocumentContainsAFieldWithValueGreaterThenGivenValue()
    {
        $dummyFieldName = 'field-name';

        $mockCriterion = $this->createMockCriterion($dummyFieldName, '1', 'gt');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$mockCriterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField($dummyFieldName, '2');

        $this->mockDocumentFieldsCollection->expects($this->once())
            ->method('getFields')
            ->willReturn([$mockSearchDocumentField]);

        $this->assertTrue($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    /**
     * @test
     */
    public function itShouldReturnTrueIfSearchDocumentContainsAFieldWithValueGreaterOrEqualToGivenValue()
    {
        $dummyFieldName = 'field-name';

        $mockCriterion = $this->createMockCriterion($dummyFieldName, '1', 'gte');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$mockCriterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField($dummyFieldName, '1');

        $this->mockDocumentFieldsCollection->expects($this->once())
            ->method('getFields')
            ->willReturn([$mockSearchDocumentField]);

        $this->assertTrue($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    /**
     * @test
     */
    public function itShouldReturnTrueIfSearchDocumentContainsAFieldWithValueLessThenGivenValue()
    {
        $dummyFieldName = 'field-name';

        $mockCriterion = $this->createMockCriterion($dummyFieldName, '2', 'lt');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$mockCriterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField($dummyFieldName, '1');

        $this->mockDocumentFieldsCollection->expects($this->once())
            ->method('getFields')
            ->willReturn([$mockSearchDocumentField]);

        $this->assertTrue($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    /**
     * @test
     */
    public function itShouldReturnTrueIfSearchDocumentContainsAFieldWithValueLessOrEqualToGivenValue()
    {
        $dummyFieldName = 'field-name';

        $mockCriterion = $this->createMockCriterion($dummyFieldName, '1', 'lte');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$mockCriterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField($dummyFieldName, '1');

        $this->mockDocumentFieldsCollection->expects($this->once())
            ->method('getFields')
            ->willReturn([$mockSearchDocumentField]);

        $this->assertTrue($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    /**
     * @test
     */
    public function itShouldReturnFalseIfUnknownSearchDocumentFieldComparisonOperationIsEncountered()
    {
        $mockCriterion = $this->createMockCriterion('field-name', 'field-value', 'unknown-operation');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$mockCriterion]);

        $mockSearchDocumentField = $this->createMockSearchDocumentField('field-name', 'field-value');

        $this->mockDocumentFieldsCollection->expects($this->once())
            ->method('getFields')
            ->willReturn([$mockSearchDocumentField]);

        $this->assertFalse($this->searchDocument->isMatchingCriteria($mockCriteria));
    }

    /**
     * @param string $fieldName
     * @param string $fieldValue
     * @param string $operation
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockCriterion($fieldName, $fieldValue, $operation)
    {
        $mockCriterion = $this->getMock(SearchCriterion::class, [], [], '', false);
        $mockCriterion->expects($this->any())
            ->method('getFieldName')
            ->willReturn($fieldName);
        $mockCriterion->expects($this->any())
            ->method('getFieldValue')
            ->willReturn($fieldValue);
        $mockCriterion->expects($this->any())
            ->method('getOperation')
            ->willReturn($operation);

        return $mockCriterion;
    }

    /**
     * @param string $condition
     * @param \PHPUnit_Framework_MockObject_MockObject[]
     * @return SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockCriteria($condition, array $mockCriteriaToReturn)
    {
        $mockCriteria = $this->getMock(SearchCriteria::class, [], [], '', false);
        $mockCriteria->expects($this->any())
            ->method('getCondition')
            ->willReturn($condition);
        $mockCriteria->expects($this->any())
            ->method('getCriteria')
            ->willReturn($mockCriteriaToReturn);

        return $mockCriteria;
    }

    /**
     * @param $fieldName
     * @param $fieldValue
     * @return \PHPUnit_Framework_MockObject_MockObject|SearchDocumentField
     */
    private function createMockSearchDocumentField($fieldName, $fieldValue)
    {
        $mockSearchDocumentField = $this->getMock(SearchDocumentField::class, [], [], '', false);
        $mockSearchDocumentField->expects($this->any())
            ->method('getKey')
            ->willReturn($fieldName);
        $mockSearchDocumentField->expects($this->any())
            ->method('getValue')
            ->willReturn($fieldValue);

        return $mockSearchDocumentField;
    }
}
