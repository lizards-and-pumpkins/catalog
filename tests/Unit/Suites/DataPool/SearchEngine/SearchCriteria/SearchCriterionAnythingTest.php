<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything
 */
class SearchCriterionAnythingTest extends \PHPUnit_Framework_TestCase
{
    public function testItImplementsTheSearchCriteriaInterface()
    {
        $this->assertInstanceOf(SearchCriteria::class, new SearchCriterionAnything());
    }

    public function testItImplementsJsonSerializable()
    {
        $this->assertInstanceOf(\JsonSerializable::class, new SearchCriterionAnything());
    }

    public function testItReturnsAnArrayRepresentationWhenJsonSerialized()
    {
        $result = (new SearchCriterionAnything())->jsonSerialize();
        
        $expectation = [
            'fieldName'  => '',
            'fieldValue' => '',
            'operation'  => 'Anything'
        ];

        $this->assertSame($expectation, $result);
    }
}
