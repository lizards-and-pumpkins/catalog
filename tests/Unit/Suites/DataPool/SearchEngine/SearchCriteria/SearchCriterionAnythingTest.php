<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything
 */
class SearchCriterionAnythingTest extends TestCase
{
    /**
     * @var SearchCriterionAnything
     */
    private $criteria;

    final protected function setUp(): void
    {
        $this->criteria = new SearchCriterionAnything();
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
            'fieldName'  => '',
            'fieldValue' => '',
            'operation'  => 'Anything'
        ];

        $this->assertSame($expectation, $this->criteria->jsonSerialize());
    }

    public function testReturnsArrayRepresentationOfCriteria(): void
    {
        $expectation = [
            'fieldName'  => '',
            'fieldValue' => '',
            'operation'  => 'Anything'
        ];

        $this->assertSame($expectation, $this->criteria->toArray());
    }
}
