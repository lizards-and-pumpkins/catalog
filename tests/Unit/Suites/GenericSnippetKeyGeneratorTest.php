<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Exception\InvalidSnippetCodeException;
use LizardsAndPumpkins\Exception\MissingSnippetKeyGenerationDataException;

/**
 * @covers \LizardsAndPumpkins\GenericSnippetKeyGenerator
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

        $this->stubContext = $this->getMock(Context::class);
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
        new GenericSnippetKeyGenerator($invalidSnippetType, $this->dummyContextParts, $this->dummyUsedDataParts);
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
        $this->setExpectedException(MissingSnippetKeyGenerationDataException::class);
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
