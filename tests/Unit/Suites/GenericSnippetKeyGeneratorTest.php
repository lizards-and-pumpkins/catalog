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
    private $dummySnippetCode = 'test_snippet_code';

    /**
     * @var string[]
     */
    private $dummyContextParts = ['dummy-context-part'];
    
    /**
     * @var GenericSnippetKeyGenerator
     */
    private $keyGenerator;

    public function setUp()
    {
        $this->keyGenerator = new GenericSnippetKeyGenerator($this->dummySnippetCode, $this->dummyContextParts);
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
        new GenericSnippetKeyGenerator($invalidSnippetType, $this->dummyContextParts);
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

        $this->assertContains($this->dummySnippetCode, $result);
    }

    public function testContextIdentifierIsIncludedInReturnedKey()
    {
        $dummyContextId = 'foo';
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('getIdForParts')->willReturn($dummyContextId);
        $result = $this->keyGenerator->getKeyForContext($stubContext);

        $this->assertContains($dummyContextId, $result);
    }

    public function testRequiredContextPartsAreReturned()
    {
        $result = $this->keyGenerator->getContextPartsUsedForKey();
        $this->assertSame($this->dummyContextParts, $result);
    }
}
