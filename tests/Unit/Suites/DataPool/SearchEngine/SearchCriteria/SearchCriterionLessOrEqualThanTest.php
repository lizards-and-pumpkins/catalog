<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessOrEqualThan
 */
class SearchCriterionLessOrEqualThanTest extends \PHPUnit_Framework_TestCase
{
    private $testFieldName = 'foo';

    private $testFieldValue = 'bar';

    /**
     * @var SearchCriterionLessOrEqualThan
     */
    private $criteria;

    final protected function setUp()
    {
        $this->criteria = new SearchCriterionLessOrEqualThan($this->testFieldName, $this->testFieldValue);
    }

    public function testItImplementsTheSearchCriteriaInterface()
    {
        $this->assertInstanceOf(SearchCriteria::class, $this->criteria);
    }

    public function testItImplementsJsonSerializable()
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->criteria);
    }

    public function testItReturnsAnArrayRepresentationWhenJsonSerialized()
    {
        $expectation = [
            'fieldName'  => $this->testFieldName,
            'fieldValue' => $this->testFieldValue,
            'operation'  => 'LessOrEqualThan'
        ];

        $this->assertSame($expectation, $this->criteria->jsonSerialize());
    }

    public function testReturnsArrayRepresentationOfCriteria()
    {
        $expectation = [
            'fieldName'  => $this->testFieldName,
            'fieldValue' => $this->testFieldValue,
            'operation'  => 'LessOrEqualThan'
        ];

        $this->assertSame($expectation, $this->criteria->toArray());
    }
}
