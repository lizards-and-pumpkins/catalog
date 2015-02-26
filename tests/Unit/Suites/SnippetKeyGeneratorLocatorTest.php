<?php


namespace Brera;

/**
 * @covers \Brera\SnippetKeyGeneratorLocator
 */
class SnippetKeyGeneratorLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $locator;

    protected function setUp()
    {
        $this->locator = new SnippetKeyGeneratorLocator();
    }

    /**
     * @test
     * @expectedException \Brera\InvalidSnippetCodeException
     * @expectedExceptionMessage Expected snippet code to be a string
     */
    public function itShouldOnlyTakeStringsAsSnippetCodes()
    {
        $mockSnippetRenderer = $this->getMock(SnippetRenderer::class);
        $this->locator->getKeyGeneratorForSnippetCode($mockSnippetRenderer);
    }

    /**
     * @test
     */
    public function itShouldReturnADefaultKeyGeneratorForAnUnknownCode()
    {
        $this->markTestSkipped();
        $result = $this->locator->getKeyGeneratorForSnippetCode('test');
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }
}
