<?php


namespace Brera\Context;

use Brera\DataVersion;
use Brera\Http\HttpRequest;

/**
 * @covers \Brera\Context\ContextBuilder
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\Context\ContextDecorator
 * @uses   \Brera\DataVersion
 */
class ContextBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->builder = new ContextBuilder(DataVersion::fromVersionString('1'));
    }

    /**
     * @test
     * @expectedException \Brera\Context\ContextDecoratorNotFoundException
     */
    public function itShouldThrowAnExceptionForNonExistingCode()
    {
        $contexts = ['foo' => 'bar'];
        $this->builder->getContext($contexts);
    }

    /**
     * @test
     * @expectedException \Brera\Context\InvalidContextDecoratorClassException
     */
    public function itShouldThrowExceptionForNonContextDecoratorClass()
    {
        $contexts = ['stub_invalid_test' => 'dummy'];
        $this->builder->getContext($contexts);
    }

    /**
     * @test
     */
    public function itShouldReturnContextsForGiveParts()
    {
        $contexts = [
            ['stub_valid_test' => 'dummy'],
        ];
        $result = $this->builder->getContexts($contexts);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Context::class, $result);
    }

    /**
     * @test
     * @dataProvider underscoreCodeDataProvider
     */
    public function itShouldRemoveUnderscoresFromTheKey($testCode, $expected)
    {
        $method = new \ReflectionMethod($this->builder, 'removeUnderscores');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invoke($this->builder, $testCode));
    }

    public function underscoreCodeDataProvider()
    {
        return [
            'no underscores' => ['none', 'none'],
            'one underscore' => ['customer_group', 'customerGroup'],
            'three underscores' => ['test_three_underscores', 'testThreeUnderscores'],
            'underscores front' => ['_front', 'Front'],
            'underscores end' => ['end_', 'end'],
            'consecutive underscores' => ['consecutive__underscores', 'consecutiveUnderscores'],
            'consecutive underscores front' => ['__consecutive_underscores', 'ConsecutiveUnderscores'],
            'consecutive underscores end' => ['consecutive_underscores__', 'consecutiveUnderscores'],
        ];
    }

    /**
     * @test
     * @expectedException \Brera\Context\ContextDecoratorNotFoundException
     */
    public function itShouldThrowAnExceptionWhenAddingANonExistentClass()
    {
        $this->builder->registerContextDecorator('test', 'Non\\Existent\\DecoratorClass');
    }

    /**
     * @test
     * @expectedException \Brera\Context\InvalidContextDecoratorClassException
     */
    public function itShouldThrowAnExceptionWhenAddingAnInvalidDecoratorClass()
    {
        $this->builder->registerContextDecorator('test', StubInvalidTestContextDecorator::class);
    }

    /**
     * @test
     */
    public function itShouldAllowRegisteringContextCodesToClasses()
    {
        $this->builder->registerContextDecorator('test', StubValidTestContextDecorator::class);
        $contexts = [
            ['test' => 'dummy'],
        ];
        $result = $this->builder->getContexts($contexts);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Context::class, $result);
    }

    /**
     * @test
     */
    public function itShouldCreateAnContextFromARequest()
    {
        $stubRequest = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->builder->createFromRequest($stubRequest);
        $this->assertInstanceOf(Context::class, $result);
    }
}
