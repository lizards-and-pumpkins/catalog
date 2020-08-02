<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Context\Stub\FromInputCopyingTestContextPartBuilder;
use LizardsAndPumpkins\Http\HttpRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 */
class SelfContainedContextBuilderTest extends TestCase
{
    /**
     * @var SelfContainedContextBuilder
     */
    private $contextBuilder;

    /**
     * @var ContextPartBuilder[]|MockObject[]
     */
    private $stubContextPartBuilders;

    /**
     * @param string $code
     * @param string|null $value
     * @return ContextPartBuilder|MockObject
     */
    private function createMockContextPartBuilder(string $code, $value) : ContextPartBuilder
    {
        $stubContextPartBuilder = $this->createMock(ContextPartBuilder::class);
        $stubContextPartBuilder->method('getCode')->willReturn($code);
        $stubContextPartBuilder->method('getValue')->willReturn($value);
        return $stubContextPartBuilder;
    }

    final protected function setUp(): void
    {
        $this->stubContextPartBuilders = [
            $this->createMockContextPartBuilder('aaa', 'value_a'),
            $this->createMockContextPartBuilder('bbb', 'value_b'),
            $this->createMockContextPartBuilder('ccc', null),
        ];
        $this->contextBuilder = new SelfContainedContextBuilder(...$this->stubContextPartBuilders);
    }

    public function testItIsAContextBuilder(): void
    {
        $this->assertInstanceOf(ContextBuilder::class, $this->contextBuilder);
    }

    public function testItReturnsAContextInstance(): void
    {
        $this->assertInstanceOf(Context::class, $this->contextBuilder->createContext([]));
    }

    public function testItDelegatesToTheInjectedContextPartBuildersToBuildTheContextArray(): void
    {
        $context = $this->contextBuilder->createContext([]);
        $this->assertTrue($context->supportsCode('aaa'));
        $this->assertTrue($context->supportsCode('bbb'));
    }

    public function testItIgnoresContextPartBuildersThatReturnNull(): void
    {
        $context = $this->contextBuilder->createContext([]);
        $this->assertFalse($context->supportsCode('ccc'));
        $this->assertNotContains('ccc', $context->getSupportedCodes());
    }

    public function testItReturnsAContextFromTheRequest(): void
    {
        /** @var HttpRequest|MockObject $stubRequest */
        $stubRequest = $this->createMock(HttpRequest::class);
        $this->stubContextPartBuilders[0]->expects($this->once())
            ->method('getValue')
            ->with($this->arrayHasKey(ContextBuilder::REQUEST));
        $this->assertInstanceOf(Context::class, $this->contextBuilder->createFromRequest($stubRequest));
    }

    public function testItReturnsOneContextForEachDataSet(): void
    {
        $dataSets = [ [], [], [] ];
        $result = $this->contextBuilder->createContextsFromDataSets($dataSets);
        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(Context::class, $result);
        $this->assertCount(3, $result);
    }

    public function testItPassesTheGivenArrayStraightToTheContextForRehydration(): void
    {
        $context = SelfContainedContextBuilder::rehydrateContext(['foo' => 'bar', 'baz' => 'qux']);
        $this->assertInstanceOf(Context::class, $context);
        $this->assertSame(['foo', 'baz'], $context->getSupportedCodes());
        $this->assertSame('bar', $context->getValue('foo'));
        $this->assertSame('qux', $context->getValue('baz'));
    }

    public function testItReturnsAnExpandedContext(): void
    {
        $fooContextPart = new FromInputCopyingTestContextPartBuilder('foo');
        $bazContextPart = new FromInputCopyingTestContextPartBuilder('baz');
        $builder = new SelfContainedContextBuilder($fooContextPart, $bazContextPart);
        
        $originalContext = $builder->createContext(['foo' => 'bar']);
        $expandedContext = $builder->expandContext($originalContext, ['baz' => 'qux']);
        $this->assertInstanceOf(Context::class, $expandedContext);
        $this->assertNotSame($originalContext, $expandedContext);
        $this->assertSame('bar', $expandedContext->getValue('foo'));
        $this->assertSame('qux', $expandedContext->getValue('baz'));
    }
}
