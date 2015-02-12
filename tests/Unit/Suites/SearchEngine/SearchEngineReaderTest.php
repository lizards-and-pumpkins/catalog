<?php

namespace Brera\SearchEngine;

/**
 * @covers \Brera\SearchEngine\SearchEngineReader
 */
class SearchEngineReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchEngineReader
     */
    private $reader;

    /**
     * @var SearchEngine|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchEngine;

    protected function setUp()
    {
        $this->stubSearchEngine = $this->getMock(SearchEngine::class);

        $this->reader = new SearchEngineReader($this->stubSearchEngine);
    }

    /**
     * @test
     */
    public function itShouldQuerySearchEngine()
    {
        $this->stubSearchEngine->expects($this->once())
            ->method('query');

        $this->reader->getSearchResults('foo');
    }
}
