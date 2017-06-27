<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\MissingSnippetKeyGenerationDataException;
use LizardsAndPumpkins\Import\SnippetCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\Import\SnippetCode
 */
class GenericSnippetKeyGeneratorTest extends TestCase
{
    /**
     * @var SnippetCode
     */
    private $dummySnippetCode;

    /**
     * @var string[]
     */
    private $dummyContextParts = ['dummy-context-part'];

    /**
     * @var string[]
     */
    private $dummyUsedDataParts = ['foo'];

    /**
     * @var GenericSnippetKeyGenerator
     */
    private $keyGenerator;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    public function setUp()
    {
        $this->dummySnippetCode = new SnippetCode('test_snippet_code');

        $this->keyGenerator = new GenericSnippetKeyGenerator(
            $this->dummySnippetCode,
            $this->dummyContextParts,
            $this->dummyUsedDataParts
        );

        $this->stubContext = $this->createMock(Context::class);
    }

    public function testSnippetKeyGeneratorInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetKeyGenerator::class, $this->keyGenerator);
    }

    public function testSnippetKeyContainsSnippetCode()
    {
        $result = $this->keyGenerator->getKeyForContext($this->stubContext, ['foo' => 'bar']);
        $this->assertContains((string) $this->dummySnippetCode, $result);
    }

    public function testSnippetKeyContainsContextPartValue()
    {
        $dummyContextId = 'foo';
        $this->stubContext->method('getIdForParts')->willReturn($dummyContextId);
        $result = $this->keyGenerator->getKeyForContext($this->stubContext, ['foo' => 'bar']);

        $this->assertContains($dummyContextId, $result);
    }

    public function testExceptionIsThrownIfUsedDataPartIsNotPresent()
    {
        $this->expectException(MissingSnippetKeyGenerationDataException::class);
        $this->keyGenerator->getKeyForContext($this->stubContext, []);
    }

    public function testSnippetKeyContainsOnlySpecifiedPartsOfDataValue()
    {
        $dummyData = ['foo' => 'bar', 'baz' => 'qux'];
        $result = $this->keyGenerator->getKeyForContext($this->stubContext, $dummyData);

        $this->assertContains('bar', $result);
        $this->assertNotContains('qux', $result);
    }
}
