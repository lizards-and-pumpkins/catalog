<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything
 */
class SearchCriterionAnythingTest extends \PHPUnit_Framework_TestCase
{
    public function testItImplementsTheSearchCriteriaInterface()
    {
        $this->assertInstanceOf(SearchCriteria::class, SearchCriterionAnything::create());
    }

    public function testItMatchesAnySearchDocument()
    {
        $mockSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $this->assertTrue(SearchCriterionAnything::create()->matches($mockSearchDocument));
    }

    public function testItImplementsJsonSerializable()
    {
        $this->assertInstanceOf(\JsonSerializable::class, SearchCriterionAnything::create());
    }

    public function testItReturnsAnArrayRepresentationWhenJsonSerialized()
    {
        $result = SearchCriterionAnything::create()->jsonSerialize();
        
        $expectation = [
            'fieldName'  => '',
            'fieldValue' => '',
            'operation'  => 'Anything'
        ];

        $this->assertSame($expectation, $result);
    }
}
