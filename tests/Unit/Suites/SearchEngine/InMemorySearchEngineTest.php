<?php

namespace Brera\SearchEngine;

/**
 * @covers \Brera\SearchEngine\InMemorySearchEngine
 */
class InMemorySearchEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemorySearchEngine
     */
    private $searchEngine;

    protected function setUp()
    {
        $this->searchEngine = new InMemorySearchEngine();
    }

    /**
     * @test
     */
    public function itShouldImplementSearchEngineInterface()
    {
        $this->assertInstanceOf(SearchEngine::class, $this->searchEngine);
    }

    /**
     * @test
     */
    public function itShouldReturnAnEmptyArrayWhateverIsAskedIfIndexIsEmpty()
    {
        $result = $this->searchEngine->query('bar');

        $this->assertCount(0, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnAnEmptyArrayIfQueryStringIsNotFoundInIndex()
    {
        $this->searchEngine->addToIndex(['foo' => 'bar']);
        $result = $this->searchEngine->query('baz');

        $this->assertCount(0, $result);
    }

    /**
     * @test
     */
    public function itShouldAddEntryIntoIndexAndThenFindIt()
    {
        $this->searchEngine->addToIndex(['foo' => 'bar']);
        $result = $this->searchEngine->query('bar');

        $this->assertCount(1, $result);
        $this->assertContains(['foo' => 'bar'], $result);
    }

    /**
     * @test
     */
    public function itShouldAddMultipleEntriesToIndex()
    {
        $this->searchEngine->addMultiToIndex([['foo' => 'bar'], ['baz' => 'bar']]);
        $result = $this->searchEngine->query('bar');

        $this->assertCount(2, $result);
        $this->assertContains(['foo' => 'bar'], $result);
        $this->assertContains(['baz' => 'bar'], $result);
    }

    /**
     * @test
     */
    public function itShouldReturnOnlyEntriesContainingRequestedString()
    {
        $this->searchEngine->addMultiToIndex([['foo' => 'bar'], ['baz' => 'qux']]);
        $result = $this->searchEngine->query('bar');

        $this->assertCount(1, $result);
        $this->assertContains(['foo' => 'bar'], $result);
    }

    /**
     * @test
     */
    public function itShouldReturnEntriesContainingRequestedString()
    {
        $this->searchEngine->addMultiToIndex([['foo' => 'barbarism'], ['baz' => 'toolbar']]);
        $result = $this->searchEngine->query('bar');

        $this->assertCount(2, $result);
        $this->assertContains(['foo' => 'barbarism'], $result);
        $this->assertContains(['baz' => 'toolbar'], $result);
    }
}
