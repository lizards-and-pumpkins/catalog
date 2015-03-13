<?php

namespace Brera;

/**
 * @covers \Brera\MissingSnippetCodeMessage
 */
class MissingSnippetCodeMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MissingSnippetCodeMessage
     */
    private $message;

    /**
     * @var string[]
     */
    private $missingSnippetCodes;

    /**
     * @var string[]
     */
    private $stubContext;

    protected function setUp()
    {
        $this->missingSnippetCodes = ['foo', 'bar'];
        $this->stubContext = ['baz'];

        $this->message = new MissingSnippetCodeMessage($this->missingSnippetCodes, $this->stubContext);
    }

    /**
     * @test
     */
    public function itShouldReturnLogMessage()
    {
        $expectation = 'Snippets listed in the page meta information where not loaded from the data pool (foo, bar)';

        $this->assertEquals($expectation, (string) $this->message);
    }

    /**
     * @test
     */
    public function itShouldReturnContext()
    {
        $result = $this->message->getContext();

        $this->assertSame($this->stubContext, $result);
    }
}
