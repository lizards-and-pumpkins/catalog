<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaAnything
 */
class SearchCriteriaAnythingTest extends \PHPUnit_Framework_TestCase
{
    public function testItImplementsTheSearchCriteriaInterface()
    {
        $this->assertInstanceOf(SearchCriteria::class, SearchCriteriaAnything::create());
    }

    public function testItMatchesAnySearchDocument()
    {
        $mockSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $this->assertTrue(SearchCriteriaAnything::create()->matches($mockSearchDocument));
    }

    public function testItImplementsJsonSerializable()
    {
        $this->assertInstanceOf(\JsonSerializable::class, SearchCriteriaAnything::create());
    }

    public function testItReturnsAnArrayRepresentationWhenJsonSerialized()
    {
        $result = SearchCriteriaAnything::create()->jsonSerialize();
        
        $expectation = [
            'fieldName'  => '',
            'fieldValue' => '',
            'operation'  => 'Anything'
        ];

        $this->assertSame($expectation, $result);
    }
}
