<?php


namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Context\Stubs\FromInputCopyingTestContextPartBuilder;
use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 */
class SelfContainedContextBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SelfContainedContextBuilder
     */
    private $contextBuilder;

    /**
     * @var ContextPartBuilder[]|\PHPUnit_Framework_MockObject_MockObject[]
     */
    private $stubContextPartBuilders;

    /**
     * @param string $code
     * @param string|null $value
     * @return ContextPartBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockContextPartBuilder($code, $value)
    {
        $stubContextPartBuilder = $this->getMock(ContextPartBuilder::class);
        $stubContextPartBuilder->method('getCode')->willReturn($code);
        $stubContextPartBuilder->method('getValue')->willReturn($value);
        return $stubContextPartBuilder;
    }

    protected function setUp()
    {
        $this->stubContextPartBuilders = [
            $this->createMockContextPartBuilder('aaa', 'value_a'),
            $this->createMockContextPartBuilder('bbb', 'value_b'),
            $this->createMockContextPartBuilder('ccc', null),
        ];
        $this->contextBuilder = new SelfContainedContextBuilder(...$this->stubContextPartBuilders);
    }

    public function testItIsAContextBuilder()
    {
        $this->assertInstanceOf(ContextBuilder::class, $this->contextBuilder);
    }

    public function testItReturnsAContextInstance()
    {
        $this->assertInstanceOf(Context::class, $this->contextBuilder->createContext([]));
    }

    public function testItDelegatesToTheInjectedContextPartBuildersToBuildTheContextArray()
    {
        $context = $this->contextBuilder->createContext([]);
        $this->assertTrue($context->supportsCode('aaa'));
        $this->assertTrue($context->supportsCode('bbb'));
    }

    public function testItIgnoresContextPartBuildersThatReturnNull()
    {
        $context = $this->contextBuilder->createContext([]);
        $this->assertFalse($context->supportsCode('ccc'));
        $this->assertNotContains('ccc', $context->getSupportedCodes());
    }

    public function testItReturnsAContextFromTheRequest()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $this->stubContextPartBuilders[0]->expects($this->once())
            ->method('getValue')
            ->with($this->arrayHasKey(ContextBuilder::REQUEST));
        $this->assertInstanceOf(Context::class, $this->contextBuilder->createFromRequest($stubRequest));
    }

    public function testItReturnsOneContextForEachDataSet()
    {
        $dataSets = [ [], [], [] ];
        $result = $this->contextBuilder->createContextsFromDataSets($dataSets);
        $this->assertInternalType('array', $result);
        $this->assertContainsOnlyInstancesOf(Context::class, $result);
        $this->assertCount(3, $result);
    }

    public function testItPassesTheGivenArrayStraightToTheContextForRehydration()
    {
        $context = SelfContainedContextBuilder::rehydrateContext(['foo' => 'bar', 'baz' => 'qux']);
        $this->assertInstanceOf(Context::class, $context);
        $this->assertSame(['foo', 'baz'], $context->getSupportedCodes());
        $this->assertSame('bar', $context->getValue('foo'));
        $this->assertSame('qux', $context->getValue('baz'));
    }

    public function testItReturnsAnExpandedContext()
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
