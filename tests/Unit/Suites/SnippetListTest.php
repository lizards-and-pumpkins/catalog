<?php

namespace LizardsAndPumpkins;

/**
 * @covers \LizardsAndPumpkins\SnippetList
 * @uses   \LizardsAndPumpkins\Snippet
 */
class SnippetListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetList
     */
    private $snippetList;

    public function setUp()
    {
        $this->snippetList = new SnippetList();
    }

    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\IteratorAggregate::class, $this->snippetList);
    }

    public function testIsInitiallyEmpty()
    {
        $this->assertCount(0, $this->snippetList);
    }

    public function testSnippetIsAdded()
    {
        $snippet = Snippet::create('test', 'test');
        $this->snippetList->add($snippet);

        $this->assertEquals(1, $this->snippetList->count());
    }

    public function testSnippetIsReturned()
    {
        $snippet = Snippet::create('test', 'test');
        $this->snippetList->add($snippet);

        $this->assertContains($snippet, $this->snippetList->getIterator());
    }

    public function testTwoSnippetListsAreMerged()
    {
        $snippet = Snippet::create('test', 'test');
        $this->snippetList->add($snippet);

        $snippet2 = Snippet::create('test', 'test');
        $resultList2 = new SnippetList();
        $resultList2->add($snippet2);

        $this->snippetList->merge($resultList2);

        $this->assertEquals(2, $this->snippetList->count());
        $this->assertContains($snippet, $this->snippetList->getIterator());
        $this->assertContains($snippet2, $this->snippetList->getIterator());
    }

    public function testSnippetsCanBeSetAsConstructorArguments()
    {
        $snippet1 = Snippet::create('test1', 'test');
        $snippet2 = Snippet::create('test2', 'test');
        
        $snippetList = new SnippetList($snippet1, $snippet2);
        
        $this->assertCount(2, $snippetList);
        $this->assertContains($snippet1, $snippetList->getIterator());
        $this->assertContains($snippet2, $snippetList->getIterator());
    }
}
