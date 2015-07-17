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
    private $contentBlockId = 'foo';
    
    private $testSnippetCode = 'content_block';

    /**
     * @var ContentBlockSnippetKeyGenerator
     */
    private $keyGenerator;

    /**
     * @return Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockContext()
    {
        return $this->getMock(Context::class);
    }

    protected function setUp()
    {
        $this->keyGenerator = new ContentBlockSnippetKeyGenerator($this->testSnippetCode);
    }

    public function testSnippetKeyGeneratorInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetKeyGenerator::class, $this->keyGenerator);
    }

    public function testExceptionIsThrownIfTheSnippetCodeIsNotAString()
    {
        $this->setExpectedException(InvalidSnippetCodeException::class);
        new ContentBlockSnippetKeyGenerator(123);
    }

    public function testExceptionIsThrownIfNoContentBlockIdIsSpecified()
    {
        $this->setExpectedException(MissingContentBlockIdException::class);
        $this->keyGenerator->getKeyForContext($this->getMockContext());
    }

    public function testWebsiteAndLanguageContextPartsAreUsed()
    {
        $result = $this->keyGenerator->getContextPartsUsedForKey();
        $this->assertInternalType('array', $result);
        $this->assertContains('website', $result);
        $this->assertContains('language', $result);
    }

    public function testSnippetCodeIsIncludedInTheKey()
    {
        $result = $this->keyGenerator->getKeyForContext(
            $this->getMockContext(),
            ['content_block_id' => $this->contentBlockId]
        );
        $this->assertContains($this->testSnippetCode, $result);
    }

    public function testContentBlockIdIsIncludedInTheKey()
    {
        $result = $this->keyGenerator->getKeyForContext(
            $this->getMockContext(),
            ['content_block_id' => $this->contentBlockId]
        );
        $this->assertContains((string) $this->contentBlockId, $result);
    }

    public function testContextIsIncludedIdInTheKey()
    {
        $testContextId = 'test-context-id';
        $mockContext = $this->getMockContext();
        $mockContext->expects($this->once())->method('getId')->willReturn($testContextId);
        $result = $this->keyGenerator->getKeyForContext($mockContext, ['content_block_id' => $this->contentBlockId]);

        $this->assertContains($testContextId, $result);
    }
}
