<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessThan
 */
class SearchCriterionLessThanTest extends TestCase
{
    private $testFieldName = 'foo';

    private $testFieldValue = 'bar';

    /**
     * @var SearchCriterionLessThan
     */
    private $criteria;

    final protected function setUp(): void
    {
        $this->criteria = new SearchCriterionLessThan($this->testFieldName, $this->testFieldValue);
    }

    public function testItImplementsTheSearchCriteriaInterface(): void
    {
        $this->assertInstanceOf(SearchCriteria::class, $this->criteria);
    }

    public function testItImplementsJsonSerializable(): void
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->criteria);
    }

    public function testItReturnsAnArrayRepresentationWhenJsonSerialized(): void
    {
        $expectation = [
            'fieldName'  => $this->testFieldName,
            'fieldValue' => $this->testFieldValue,
            'operation'  => 'LessThan'
        ];

        $this->assertSame($expectation, $this->criteria->jsonSerialize());
    }

    public function testReturnsArrayRepresentationOfCriteria(): void
    {
        $expectation = [
            'fieldName'  => $this->testFieldName,
            'fieldValue' => $this->testFieldValue,
            'operation'  => 'LessThan'
        ];

        $this->assertSame($expectation, $this->criteria->toArray());
    }
}
