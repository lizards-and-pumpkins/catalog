<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual
 */
class SearchCriterionEqualTest extends TestCase
{
    private $testFieldName = 'foo';

    private $testFieldValue = 'bar';

    /**
     * @var SearchCriterionEqual
     */
    private $criteria;

    final protected function setUp(): void
    {
        $this->criteria = new SearchCriterionEqual($this->testFieldName, $this->testFieldValue);
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
            'operation'  => 'Equal'
        ];

        $this->assertSame($expectation, $this->criteria->jsonSerialize());
    }

    public function testReturnsArrayRepresentationOfCriteria(): void
    {
        $expectation = [
            'fieldName'  => $this->testFieldName,
            'fieldValue' => $this->testFieldValue,
            'operation'  => 'Equal'
        ];

        $this->assertSame($expectation, $this->criteria->toArray());
    }
}
