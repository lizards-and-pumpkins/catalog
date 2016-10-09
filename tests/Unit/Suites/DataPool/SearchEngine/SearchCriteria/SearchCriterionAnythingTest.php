<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything
 */
class SearchCriterionAnythingTest extends \PHPUnit_Framework_TestCase
{
    public function testItImplementsTheSearchCriteriaInterface()
    {
        $this->assertInstanceOf(SearchCriteria::class, new SearchCriterionAnything());
    }

    public function testItMatchesAnySearchDocument()
    {
        $mockSearchDocument = $this->createMock(SearchDocument::class);
        $this->assertTrue((new SearchCriterionAnything())->matches($mockSearchDocument));
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
