<?php

namespace Brera;

/**
 * @covers \Brera\MissingSnippetCodeMessage
 */
class MissingSnippetCodeMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldImplementLogMessageInterface()
    {
        $message = new MissingSnippetCodeMessage(['foo']);

        $this->assertInstanceOf(LogMessage::class, $message);
    }

    /**
     * @test
     */
    public function itShouldReturnAMessage()
    {
        $message = new MissingSnippetCodeMessage(['foo', 'bar']);
        $result = $message->getMessage();

        $expectation = 'Snippets listed in the page meta information where not loaded from the data pool (foo, bar)';

        $this->assertEquals($expectation, $result);
    }
}
