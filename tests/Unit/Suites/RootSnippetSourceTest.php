<?php

namespace Brera;

use Brera\Context\Context;

/**
 * @covers \Brera\RootSnippetSource
 */
class RootSnippetSourceTest extends \PHPUnit_Framework_TestCase
{
    public function testRootSnippetSourceContextAndNumberOfItemsPerPageAreReturned()
    {
        $stubContext = $this->getMock(Context::class);
        $rootSnippetSource = new RootSnippetSource($stubContext, 1);

        $context = $rootSnippetSource->getContext();
        $numItemsPerPage = $rootSnippetSource->getNumItemsPerPage();

        $this->assertSame($stubContext, $context);
        $this->assertEquals(1, $numItemsPerPage);
    }
}
