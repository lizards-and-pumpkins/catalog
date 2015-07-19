<?php

namespace Brera\Content;

use Brera\Context\Context;
use Brera\InvalidSnippetCodeException;
use Brera\SnippetKeyGenerator;

/**
 * @covers \Brera\Content\ContentBlockSnippetKeyGenerator
 */
class ContentBlockSnippetKeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $contentBlockId = 'foo';

    /**
     * @var string
     */
    private $dummySnippetCode = 'content_block';

    /**
     * @var string[]
     */
    private $dummyContextParts = ['dummy-context-part'];

    /**
     * @var ContentBlockSnippetKeyGenerator
     */
    private $keyGenerator;

    protected function setUp()
    {
        $this->keyGenerator = new ContentBlockSnippetKeyGenerator($this->dummySnippetCode, $this->dummyContextParts);
    }

    public function testSnippetKeyGeneratorInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetKeyGenerator::class, $this->keyGenerator);
    }

    public function testExceptionIsThrownIfTheSnippetCodeIsNotAString()
    {
        $this->setExpectedException(InvalidSnippetCodeException::class);
        new ContentBlockSnippetKeyGenerator(123, $this->dummyContextParts);
    }

    public function testExceptionIsThrownIfNoContentBlockIdIsSpecified()
    {
        $this->setExpectedException(MissingContentBlockIdException::class);
        $stubContext = $this->getMock(Context::class);
        $this->keyGenerator->getKeyForContext($stubContext, []);
    }

    public function testRequiredContextPartsAreReturned()
    {
        $result = $this->keyGenerator->getContextPartsUsedForKey();
        $this->assertSame($this->dummyContextParts, $result);
    }

    public function testSnippetCodeIsIncludedInTheKey()
    {
        $stubContext = $this->getMock(Context::class);
        $result = $this->keyGenerator->getKeyForContext($stubContext, ['content_block_id' => $this->contentBlockId]);

        $this->assertContains($this->dummySnippetCode, $result);
    }

    public function testContentBlockIdIsIncludedInTheKey()
    {
        $stubContext = $this->getMock(Context::class);
        $result = $this->keyGenerator->getKeyForContext($stubContext, ['content_block_id' => $this->contentBlockId]);

        $this->assertContains((string) $this->contentBlockId, $result);
    }

    public function testContextIdentifierIsIncludedInReturnedKey()
    {
        $dummyContextId = 'foo';
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('getIdForParts')->willReturn($dummyContextId);
        $result = $this->keyGenerator->getKeyForContext($stubContext, ['content_block_id' => $this->contentBlockId]);

        $this->assertContains($dummyContextId, $result);
    }
}
