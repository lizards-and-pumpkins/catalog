<?php

namespace Brera\Context;

use Brera\SampleContextSource;

/**
 * @covers \Brera\Context\ContextSource
 * @covers \Brera\SampleContextSource
 */
class ContextSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnAnArray()
    {
        $stubContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);
        $stubContextBuilder->expects($this->any())
            ->method('getContexts')
            ->willReturn([]);

        $contextSource = new SampleContextSource($stubContextBuilder);
        $result = $contextSource->getAllAvailableContexts();

        $this->assertInternalType('array', $result);
    }
}
