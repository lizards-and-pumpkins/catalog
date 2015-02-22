<?php

namespace Brera\DataPool\SearchEngine;

/**
 * @covers \Brera\DataPool\SearchEngine\InMemorySearchEngine
 */
class InMemorySearchEngineTest extends AbstractSearchEngineTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->searchEngine = new InMemorySearchEngine();
    }
}
