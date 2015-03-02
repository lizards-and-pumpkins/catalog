<?php

namespace Brera;

/**
 * @covers \Brera\RootSnippetSource
 */
class RootSnippetSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldImplementProjectionSourceData()
    {
        $rootSnippetSource = new RootSnippetSource();

        $this->assertInstanceOf(ProjectionSourceData::class, $rootSnippetSource);
    }
}
