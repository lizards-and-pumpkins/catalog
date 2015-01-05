<?php

namespace Brera;

class SnippetResultListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetResultList
     */
    private $resultList;

    public function setUp()
    {
        $this->resultList = new SnippetResultList();
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
    public function itShouldAddASnippetResult()
    {
        $snippet = SnippetResult::create('test', 'test');
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
        $snippet = SnippetResult::create('test', 'test');
        $this->resultList->add($snippet);
        $this->assertContains($snippet, $this->resultList->getIterator());
    }

    /**
     * @test
     */
    public function itShouldMergeTwoLists()
    {
        $snippet = SnippetResult::create('test', 'test');
        $this->resultList->add($snippet);

        $snippet2 = SnippetResult::create('test', 'test');
        $resultList2 = new SnippetResultList();
        $resultList2->add($snippet2);

        $this->resultList->merge($resultList2);

        $this->assertEquals(2, $this->resultList->count());
        $this->assertContains($snippet, $this->resultList->getIterator());
        $this->assertContains($snippet2, $this->resultList->getIterator());
    }
}
