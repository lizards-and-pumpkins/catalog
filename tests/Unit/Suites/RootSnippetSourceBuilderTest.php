<?php

namespace Brera;

/**
 * @covers \Brera\RootSnippetSourceBuilder
 */
class RootSnippetSourceBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RootSnippetSourceBuilder
     */
    private $rootSnippetSourceBuilder;

    protected function setUp()
    {
        $this->rootSnippetSourceBuilder = new RootSnippetSourceBuilder();
    }

    /**
     * @test
     */
    public function itShouldCreateARootSnippetSourceFromXml()
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing-root-snippet.xml');

        $rootSnippetSource = $this->rootSnippetSourceBuilder->createFromXml($xml);

        $this->assertInstanceOf(RootSnippetSource::class, $rootSnippetSource);
    }
}
