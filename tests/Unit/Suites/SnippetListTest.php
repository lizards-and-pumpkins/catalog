<?php

namespace LizardsAndPumpkins;

/**
 * @covers \LizardsAndPumpkins\SnippetList
 * @uses   \LizardsAndPumpkins\Snippet
 */
class SnippetListTest extends \PHPUnit_Framework_TestCase
{
    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $result = new SnippetList();
        $this->assertInstanceOf(\IteratorAggregate::class, $result);
    }

    public function testIsInitiallyEmpty()
    {
        $result = new SnippetList();
        $this->assertCount(0, $result);
    }

    public function testSnippetIsAdded()
    {
        $snippet = Snippet::create('test', 'test');
        $result = new SnippetList($snippet);

        $this->assertCount(1, $result);
        $this->assertEquals([$snippet], iterator_to_array($result));
    }

    public function testTwoSnippetListsAreMerged()
    {
        $snippet = Snippet::create('test', 'test');

        $snippetListA = new SnippetList($snippet);
        $snippetListB = new SnippetList($snippet);

        $snippetListA->merge($snippetListB);

        $this->assertCount(2, $snippetListA);
        $this->assertEquals([$snippet, $snippet], iterator_to_array($snippetListA));
    }
}
