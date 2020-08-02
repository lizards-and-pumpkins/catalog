<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionFullText
 */
class SearchCriterionFullTextTest extends TestCase
{
    private $testFieldValue = 'bar';

    /**
     * @var SearchCriterionFullText
     */
    private $criteria;

    final protected function setUp(): void
    {
        $this->criteria = new SearchCriterionFullText($this->testFieldValue);
    }

    public function testImplementsSearchCriteriaInterface(): void
    {
        $this->assertInstanceOf(SearchCriteria::class, $this->criteria);
    }

    public function testItImplementsJsonSerializable(): void
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->criteria);
    }

    public function testThrowsAnExceptionIfFieldValueIsNonString(): void
    {
        $this->expectException(\TypeError::class);
        new SearchCriterionFullText(1);
    }

    public function testItReturnsAnArrayRepresentationWhenJsonSerialized(): void
    {
        $expectation = [
            'fieldName'  => '',
            'fieldValue' => $this->testFieldValue,
            'operation'  => 'FullText'
        ];

        $this->assertSame($expectation, $this->criteria->jsonSerialize());
    }

    public function testReturnsArrayRepresentationOfCriteria(): void
    {
        $expectation = [
            'fieldName'  => '',
            'fieldValue' => $this->testFieldValue,
            'operation'  => 'FullText'
        ];

        $this->assertSame($expectation, $this->criteria->toArray());
    }
}
