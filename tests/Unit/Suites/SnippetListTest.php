<?php

namespace Brera;

/**
 * @covers \Brera\SnippetList
 * @uses   \Brera\Snippet
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

    /**
     * @test
     */
    public function itShouldBeEmptyOnCreation()
    {
        $this->assertInstanceOf(\Countable::class, $this->resultList);
        $this->assertEquals(0, $this->resultList->count());
    }

    /**
     * @test
     */
    public function itShouldAddASnippet()
    {
        $snippet = Snippet::create('test', 'test');
        $this->resultList->add($snippet);
        $this->assertEquals(1, $this->resultList->count());
    }

    /**
     * @test
     */
    public function itShouldBeIterable()
    {
        $this->assertInstanceOf(\IteratorAggregate::class, $this->resultList);
    }

    /**
     * @test
     */
    public function itShouldReturnASnippet()
    {
        $snippet = Snippet::create('test', 'test');
        $this->resultList->add($snippet);
        $this->assertContains($snippet, $this->resultList->getIterator());
    }

    /**
     * @test
     */
    public function itShouldMergeTwoLists()
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

    /**
     * @test
     */
    public function itShouldClearTheList()
    {
        $snippet = Snippet::create('test', 'test');
        $this->resultList->add($snippet);
        $this->assertEquals(1, $this->resultList->count());
        $this->resultList->clear();
        $this->assertEquals(0, $this->resultList->count());
    }
}
