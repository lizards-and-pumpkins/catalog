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

    protected function setUp()
    {
        $this->criteria = new SearchCriterionFullText($this->testFieldValue);
    }

    public function testImplementsSearchCriteriaInterface()
    {
        $this->assertInstanceOf(SearchCriteria::class, $this->criteria);
    }

    public function testItImplementsJsonSerializable()
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->criteria);
    }

    public function testThrowsAnExceptionIfFieldValueIsNonString()
    {
        $this->expectException(\TypeError::class);
        new SearchCriterionFullText(1);
    }

    public function testItReturnsAnArrayRepresentationWhenJsonSerialized()
    {
        $expectation = [
            'fieldName'  => '',
            'fieldValue' => $this->testFieldValue,
            'operation'  => 'FullText'
        ];

        $this->assertSame($expectation, $this->criteria->jsonSerialize());
    }

    public function testReturnsArrayRepresentationOfCriteria()
    {
        $expectation = [
            'fieldName'  => '',
            'fieldValue' => $this->testFieldValue,
            'operation'  => 'FullText'
        ];

        $this->assertSame($expectation, $this->criteria->toArray());
    }
}
