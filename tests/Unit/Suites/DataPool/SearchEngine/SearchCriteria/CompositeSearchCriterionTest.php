<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual
 */
class CompositeSearchCriterionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string[] $searchDocumentFieldsData
     * @return SearchDocument
     */
    private function createStubSearchDocumentWithGivenFields(array $searchDocumentFieldsData)
    {
        $searchDocumentFieldsArray = [];

        foreach ($searchDocumentFieldsData as $fieldKey => $fieldValues) {
            $searchDocumentFieldsArray[] = $this->createStubSearchDocumentField($fieldKey, $fieldValues);
        }

        $stubSearchDocumentFieldsCollection = $this->getMock(SearchDocumentFieldCollection::class, [], [], '', false);
        $stubSearchDocumentFieldsCollection->method('getIterator')
            ->willReturn(new \ArrayIterator($searchDocumentFieldsArray));

        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $stubSearchDocument->method('getFieldsCollection')->willReturn($stubSearchDocumentFieldsCollection);

        return $stubSearchDocument;
    }

    /**
     * @param string $fieldKey
     * @param string $fieldValues
     * @return SearchDocumentField|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentField($fieldKey, $fieldValues)
    {
        $stubSearchDocumentField = $this->getMock(SearchDocumentField::class, [], [], '', false);
        $stubSearchDocumentField->method('getKey')->willReturn($fieldKey);
        $stubSearchDocumentField->method('getValues')->willReturn($fieldValues);

        return $stubSearchDocumentField;
    }

    public function testSearchCriteriaInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchCriteria::class, CompositeSearchCriterion::createAnd());
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\JsonSerializable::class, CompositeSearchCriterion::createAnd());
    }

    public function testCriteriaWithAndConditionIsCreated()
    {
        $criteria = CompositeSearchCriterion::createAnd();
        $result = $criteria->jsonSerialize();
        $expectation = ['condition' => CompositeSearchCriterion::AND_CONDITION, 'criteria' => []];

        $this->assertSame($expectation, $result);
    }

    public function testCriteriaWithOrConditionIsCreated()
    {
        $criteria = CompositeSearchCriterion::createOr();
        $result = $criteria->jsonSerialize();
        $expectation = ['condition' => CompositeSearchCriterion::OR_CONDITION, 'criteria' => []];

        $this->assertSame($expectation, $result);
    }

    public function testFalseIsReturnedIfNoneOfSearchDocumentFieldsIsNotMatchingCriteria()
    {
        $testCriterion = SearchCriterionEqual::create('foo', 'bar');

        $criteria = CompositeSearchCriterion::createOr($testCriterion);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([]);

        $this->assertFalse($criteria->matches($stubSearchDocument));
    }

    public function testFalseIsReturnedIfSearchDocumentFieldsAreNotMatchingAllCriteriaConditions()
    {
        $testCriterionA = SearchCriterionEqual::create('foo', 'bar');
        $testCriterionB = SearchCriterionEqual::create('baz', 'qux');
        $criteria = CompositeSearchCriterion::createAnd($testCriterionA, $testCriterionB);

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields(['foo' => ['bar']]);

        $this->assertFalse($criteria->matches($stubSearchDocument));
    }

    public function testTrueIsReturnedIfAllOfSearchDocumentFieldsAreMatchingCriteria()
    {
        $testCriterionA = SearchCriterionEqual::create('foo', 'bar');
        $testCriterionB = SearchCriterionEqual::create('baz', 'qux');
        $criteria = CompositeSearchCriterion::createAnd($testCriterionA, $testCriterionB);

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields(['foo' => ['bar'], 'baz' => ['qux']]);

        $this->assertTrue($criteria->matches($stubSearchDocument));
    }

    public function testTrueIsReturnedIfAtLeastOneCriteriaConditionMatchesAnyOfSearchDocumentFields()
    {
        $testCriterionA = SearchCriterionEqual::create('foo', 'bar');
        $testCriterionB = SearchCriterionEqual::create('baz', 'qux');
        $criteria = CompositeSearchCriterion::createOr($testCriterionA, $testCriterionB);

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields(['foo' => ['bar']]);

        $this->assertTrue($criteria->matches($stubSearchDocument));
    }
}
