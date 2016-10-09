<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\Exception\InvalidCriterionConditionException;
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
    private function createStubSearchDocumentWithGivenFields(array $searchDocumentFieldsData) : SearchDocument
    {
        $searchDocumentFieldsArray = [];

        foreach ($searchDocumentFieldsData as $fieldKey => $fieldValues) {
            $searchDocumentFieldsArray[] = $this->createStubSearchDocumentField($fieldKey, $fieldValues);
        }

        $stubSearchDocumentFieldsCollection = $this->createMock(SearchDocumentFieldCollection::class);
        $stubSearchDocumentFieldsCollection->method('getIterator')
            ->willReturn(new \ArrayIterator($searchDocumentFieldsArray));

        $stubSearchDocument = $this->createMock(SearchDocument::class);
        $stubSearchDocument->method('getFieldsCollection')->willReturn($stubSearchDocumentFieldsCollection);

        return $stubSearchDocument;
    }

    /**
     * @param string $fieldKey
     * @param string[] $fieldValues
     * @return SearchDocumentField
     */
    private function createStubSearchDocumentField(string $fieldKey, array $fieldValues) : SearchDocumentField
    {
        $stubSearchDocumentField = $this->createMock(SearchDocumentField::class);
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
        $testCriterion = new SearchCriterionEqual('foo', 'bar');

        $criteria = CompositeSearchCriterion::createOr($testCriterion);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([]);

        $this->assertFalse($criteria->matches($stubSearchDocument));
    }

    public function testFalseIsReturnedIfSearchDocumentFieldsAreNotMatchingAllCriteriaConditions()
    {
        $testCriterionA = new SearchCriterionEqual('foo', 'bar');
        $testCriterionB = new SearchCriterionEqual('baz', 'qux');
        $criteria = CompositeSearchCriterion::createAnd($testCriterionA, $testCriterionB);

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields(['foo' => ['bar']]);

        $this->assertFalse($criteria->matches($stubSearchDocument));
    }

    public function testTrueIsReturnedIfAllOfSearchDocumentFieldsAreMatchingCriteria()
    {
        $testCriterionA = new SearchCriterionEqual('foo', 'bar');
        $testCriterionB = new SearchCriterionEqual('baz', 'qux');
        $criteria = CompositeSearchCriterion::createAnd($testCriterionA, $testCriterionB);

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields(['foo' => ['bar'], 'baz' => ['qux']]);

        $this->assertTrue($criteria->matches($stubSearchDocument));
    }

    public function testTrueIsReturnedIfAtLeastOneCriteriaConditionMatchesAnyOfSearchDocumentFields()
    {
        $testCriterionA = new SearchCriterionEqual('foo', 'bar');
        $testCriterionB = new SearchCriterionEqual('baz', 'qux');
        $criteria = CompositeSearchCriterion::createOr($testCriterionA, $testCriterionB);

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields(['foo' => ['bar']]);

        $this->assertTrue($criteria->matches($stubSearchDocument));
    }

    public function testExceptionIsThrownIfConditionIsNotSupported()
    {
        $invalidCondition = 'foo';
        $this->expectException(InvalidCriterionConditionException::class);
        CompositeSearchCriterion::create($invalidCondition);
    }

    /**
     * @dataProvider criteriaConditionProvider
     */
    public function testCriteriaWithArbitraryConditionIsCreated(string $condition)
    {
        $result = CompositeSearchCriterion::create($condition);
        $this->assertInstanceOf(CompositeSearchCriterion::class, $result);
    }

    /**
     * @return array[]
     */
    public function criteriaConditionProvider() : array
    {
        return [
            [CompositeSearchCriterion::AND_CONDITION],
            [CompositeSearchCriterion::OR_CONDITION],
        ];
    }
}
