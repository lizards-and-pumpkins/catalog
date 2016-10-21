<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\MissingSnippetKeyGenerationDataException;
use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;

/**
 * @covers \LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator
 * @covers \LizardsAndPumpkins\Util\SnippetCodeValidator
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

    public function testExceptionIsThrownDuringAttemptToCreateASnippetKeyFromNonString()
    {
        $this->expectException(\TypeError::class);
        $snippetCode = 1;
        new GenericSnippetKeyGenerator($snippetCode, $this->dummyContextParts, $this->dummyUsedDataParts);
    }

    public function testExceptionIsThrownDuringAttemptToCreateASnippetKeyFromAnEmptyString()
    {
        $this->expectException(InvalidSnippetCodeException::class);
        $snippetCode = '';
        new GenericSnippetKeyGenerator($snippetCode, $this->dummyContextParts, $this->dummyUsedDataParts);
    }

    public function testSnippetKeyContainsSnippetCode()
    {
        $result = $this->keyGenerator->getKeyForContext($this->stubContext, ['foo' => 'bar']);
        $this->assertContains($this->dummySnippetCode, $result);
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
