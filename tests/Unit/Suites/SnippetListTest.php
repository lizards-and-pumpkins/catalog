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
    private $resultList;

    public function setUp()
    {
        $this->resultList = new SnippetList();
    }

    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\IteratorAggregate::class, $this->resultList);
    }

    public function testIsInitiallyEmpty()
    {
        $this->assertCount(0, $this->resultList);
    }

    public function testSnippetIsAdded()
    {
        $snippet = Snippet::create('test', 'test');
        $this->resultList->add($snippet);

        $this->assertEquals(1, $this->resultList->count());
    }

    public function testSnippetIsReturned()
    {
        $snippet = Snippet::create('test', 'test');
        $this->resultList->add($snippet);

        $this->assertContains($snippet, $this->resultList->getIterator());
    }

    public function testTwoSnippetListsAreMerged()
    {
        $snippet = Snippet::create('test', 'test');
        $this->resultList->add($snippet);

        $snippet2 = Snippet::create('test', 'test');
        $resultList2 = new SnippetList();
        $resultList2->add($snippet2);

        $this->resultList->merge($resultList2);

        $this->assertEquals(2, $this->resultList->count());
        $this->assertContains($snippet, $this->resultList->getIterator());
        $this->assertContains($snippet2, $this->resultList->getIterator());
    }

    public function testListIsCleared()
    {
        $snippet = Snippet::create('test', 'test');

        $this->resultList->add($snippet);
        $this->assertEquals(1, $this->resultList->count());

        $this->resultList->clear();
        $this->assertEquals(0, $this->resultList->count());
    }
}
