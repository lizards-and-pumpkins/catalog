<?php

namespace Brera;

use Brera\Context\Context;

/**
 * @covers \Brera\GenericSnippetKeyGenerator
 */
class GenericSnippetKeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $testSnippetCode = 'test_snippet_code';
    
    /**
     * @var GenericSnippetKeyGenerator
     */
    private $keyGenerator;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContext;

    public function setUp()
    {
        $this->mockContext = $this->getMock(Context::class);
        $this->keyGenerator = new GenericSnippetKeyGenerator();
    }

    /**
     * @test
     */
    public function itShouldBeASnippetKeyGenerator()
    {
        $this->assertInstanceOf(SnippetKeyGenerator::class, $this->keyGenerator);
    }

    /**
     * @test
     * @dataProvider invalidTypeSnippetCodeProvider
     * @expectedException \Brera\InvalidSnippetCodeException
     */
    public function itShouldThrowAnExceptionIfTheSnippetCodeIsNoString($invalidSnippetType)
    {
        $this->keyGenerator->getKeyForContext($invalidSnippetType, 123, $this->mockContext);
    }

    /**
     * @return array[]
     */
    public function invalidTypeSnippetCodeProvider()
    {
        return [
            [12],
            [[]],
            [1.2],
            [new \stdClass()],
        ];
    }

    /**
     * @test
     */
    public function itShouldReturnAKeyIncludingTheHandle()
    {
        $result = $this->keyGenerator->getKeyForContext($this->testSnippetCode, 123, $this->mockContext);
        $this->assertContains($this->testSnippetCode, $result);
    }

    /**
     * @test
     */
    public function itShouldIncludeTheSpecifiedIdentifierInTheReturnedKey()
    {
        $result = $this->keyGenerator->getKeyForContext($this->testSnippetCode, 123, $this->mockContext);
        $this->assertContains('123', $result);
    }

    /**
     * @test
     */
    public function itShouldIncludeTheContextIdentifierInTheReturnedKey()
    {
        $testContextId = 'test-context-id';

        $this->mockContext->expects($this->any())
            ->method('getId')
            ->willReturn($testContextId);

        $result = $this->keyGenerator->getKeyForContext($this->testSnippetCode, 123, $this->mockContext);

        $this->assertContains($testContextId, $result);
    }
}
