<?php

namespace Brera;

/**
 * @covers \Brera\RootSnippetSourceBuilder
 */
class RootSnippetSourceBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \DOMDocument
     */
    private $domDocument;

    protected function setUp()
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing-root-snippet.xml');
        $this->domDocument = new \DOMDocument();
        $this->domDocument->loadXML($xml);
    }

    /**
     * @test
     */
    public function itShouldCreateARootSnippetSourceFromXml()
    {
        $rootSnippetSource = RootSnippetSourceBuilder::createFromXml($this->domDocument);

        $this->assertInstanceOf(RootSnippetSource::class, $rootSnippetSource);
    }
}
