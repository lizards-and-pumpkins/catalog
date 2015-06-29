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

    public function setUp()
    {
        $this->keyGenerator = new GenericSnippetKeyGenerator($this->testSnippetCode, ['dummy-context-part']);
    }

    public function testSnippetKeyGeneratorInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetKeyGenerator::class, $this->keyGenerator);
    }

    /**
     * @dataProvider invalidTypeSnippetCodeProvider
     * @param mixed $invalidSnippetType
     */
    public function testExceptionIsThrownIfTheSnippetCodeIsNoString($invalidSnippetType)
    {
        $this->setExpectedException(InvalidSnippetCodeException::class);
        new GenericSnippetKeyGenerator($invalidSnippetType, ['dummy-context-part']);
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

    public function testKeyIncludingHandleIsReturned()
    {
        $stubContext = $this->getMock(Context::class);
        $result = $this->keyGenerator->getKeyForContext($stubContext);

        $this->assertContains($this->testSnippetCode, $result);
    }

    public function testContextIdentifierIsIncludedInReturnedKey()
    {
        $testContextId = 'test-context-id';
        $stubContext = $this->getMock(Context::class);
        $stubContext->expects($this->once())
            ->method('getIdForParts')
            ->willReturn($testContextId);
        $result = $this->keyGenerator->getKeyForContext($stubContext);

        $this->assertContains($testContextId, $result);
    }

    public function testRequiredContextPartsAreReturned()
    {
        $result = $this->keyGenerator->getContextPartsUsedForKey();
        $this->assertInternalType('array', $result);
        $this->assertContainsOnly('string', $result);
    }
}
