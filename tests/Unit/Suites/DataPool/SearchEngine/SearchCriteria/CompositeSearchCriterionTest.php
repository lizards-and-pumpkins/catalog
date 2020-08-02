<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\Exception\InvalidCriterionConditionException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual
 */
class CompositeSearchCriterionTest extends TestCase
{
    public function testSearchCriteriaInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(SearchCriteria::class, CompositeSearchCriterion::createAnd());
    }

    public function testJsonSerializableInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(\JsonSerializable::class, CompositeSearchCriterion::createAnd());
    }

    public function testCriteriaWithAndConditionIsCreated(): void
    {
        $criteria = CompositeSearchCriterion::createAnd();
        $result = $criteria->jsonSerialize();
        $expectation = ['condition' => CompositeSearchCriterion::AND_CONDITION, 'criteria' => []];

        $this->assertSame($expectation, $result);
    }

    public function testCriteriaWithOrConditionIsCreated(): void
    {
        $criteria = CompositeSearchCriterion::createOr();
        $result = $criteria->jsonSerialize();
        $expectation = ['condition' => CompositeSearchCriterion::OR_CONDITION, 'criteria' => []];

        $this->assertSame($expectation, $result);
    }

    public function testExceptionIsThrownIfConditionIsNotSupported(): void
    {
        $invalidCondition = 'foo';
        $this->expectException(InvalidCriterionConditionException::class);
        CompositeSearchCriterion::create($invalidCondition);
    }

    /**
     * @dataProvider criteriaConditionProvider
     */
    public function testCriteriaWithArbitraryConditionIsCreated(string $condition): void
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

    public function testReturnsArrayRepresentationOfCriteria(): void
    {
        $dummyCriteriaArrayRepresentation = ['Dummy criteria array representation'];

        $stubSubCriteria = $this->createMock(SearchCriteria::class);
        $stubSubCriteria->method('toArray')->willReturn($dummyCriteriaArrayRepresentation);

        $criteria = CompositeSearchCriterion::createAnd($stubSubCriteria);

        $expectation = [
            'condition' => CompositeSearchCriterion::AND_CONDITION,
            'criteria' => [$dummyCriteriaArrayRepresentation]
        ];

        $this->assertSame($expectation, $criteria->toArray());
    }
}
