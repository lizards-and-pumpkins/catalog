<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;

/**
 * @covers \Brera\RootSnippetSourceListBuilder
 * @uses   \Brera\RootSnippetSourceList
 * @uses   \Brera\RootSnippetSource
 * @uses   \Brera\Utils\XPathParser
 */
class RootSnippetSourceListBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RootSnippetSourceListBuilder
     */
    private $rootSnippetSourceListBuilder;

    protected function setUp()
    {
        $stubContext = $this->getMock(Context::class);

        $mockContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);
        $mockContextBuilder->expects($this->any())
            ->method('getContext')
            ->willReturn($stubContext);

        $this->rootSnippetSourceListBuilder = new RootSnippetSourceListBuilder($mockContextBuilder);
    }

    public function testRootSnippetSourceListIsCreatedFromXml()
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing-root-snippet.xml');

        $rootSnippetSourceList = $this->rootSnippetSourceListBuilder->createFromXml($xml);

        $this->assertInstanceOf(RootSnippetSourceList::class, $rootSnippetSourceList);
    }
}
