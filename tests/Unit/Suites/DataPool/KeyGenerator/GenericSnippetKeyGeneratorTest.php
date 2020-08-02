<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\MissingSnippetKeyGenerationDataException;
use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator
 * @covers \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class GenericSnippetKeyGeneratorTest extends TestCase
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
     * @var string[]
     */
    private $dummyUsedDataParts = ['foo'];

    /**
     * @var GenericSnippetKeyGenerator
     */
    private $keyGenerator;

    /**
     * @var Context
     */
    private $stubContext;

    final protected function setUp(): void
    {
        $this->keyGenerator = new GenericSnippetKeyGenerator(
            $this->dummySnippetCode,
            $this->dummyContextParts,
            $this->dummyUsedDataParts
        );

        $this->stubContext = $this->createMock(Context::class);
    }

    public function testSnippetKeyGeneratorInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(SnippetKeyGenerator::class, $this->keyGenerator);
    }

    public function testExceptionIsThrownDuringAttemptToCreateASnippetKeyFromNonString(): void
    {
        $this->expectException(\TypeError::class);
        $snippetCode = 1;
        new GenericSnippetKeyGenerator($snippetCode, $this->dummyContextParts, $this->dummyUsedDataParts);
    }

    public function testExceptionIsThrownDuringAttemptToCreateASnippetKeyFromAnEmptyString(): void
    {
        $this->expectException(InvalidSnippetCodeException::class);
        $snippetCode = '';
        new GenericSnippetKeyGenerator($snippetCode, $this->dummyContextParts, $this->dummyUsedDataParts);
    }

    public function testSnippetKeyContainsSnippetCode(): void
    {
        $result = $this->keyGenerator->getKeyForContext($this->stubContext, ['foo' => 'bar']);
        $this->assertStringContainsString($this->dummySnippetCode, $result);
    }

    public function testSnippetKeyContainsContextPartValue(): void
    {
        $dummyContextId = 'foo';
        $this->stubContext->method('getIdForParts')->willReturn($dummyContextId);
        $result = $this->keyGenerator->getKeyForContext($this->stubContext, ['foo' => 'bar']);

        $this->assertStringContainsString($dummyContextId, $result);
    }

    public function testExceptionIsThrownIfUsedDataPartIsNotPresent(): void
    {
        $this->expectException(MissingSnippetKeyGenerationDataException::class);
        $this->keyGenerator->getKeyForContext($this->stubContext, []);
    }

    public function testSnippetKeyContainsOnlySpecifiedPartsOfDataValue(): void
    {
        $dummyData = ['foo' => 'bar', 'baz' => 'qux'];
        $result = $this->keyGenerator->getKeyForContext($this->stubContext, $dummyData);

        $this->assertStringContainsString('bar', $result);
        $this->assertStringNotContainsString('qux', $result);
    }
}
