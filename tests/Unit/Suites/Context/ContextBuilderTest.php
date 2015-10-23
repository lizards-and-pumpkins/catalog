<?php

namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Context\Exception\ContextDecoratorNotFoundException;
use LizardsAndPumpkins\Context\Exception\InvalidContextDecoratorClassException;
use LizardsAndPumpkins\Context\Exception\DataVersionMissingInContextDataSetException;
use LizardsAndPumpkins\Context\Stubs\TestContextDecorator;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\Context\ContextBuilder
 * @uses   \LizardsAndPumpkins\Context\VersionedContext
 * @uses   \LizardsAndPumpkins\Context\WebsiteContextDecorator
 * @uses   \LizardsAndPumpkins\Context\LocaleContextDecorator
 * @uses   \LizardsAndPumpkins\Context\ContextDecorator
 * @uses   \LizardsAndPumpkins\DataVersion
 */
class ContextBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $testVersion = '1';

    /**
     * @var ContextBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->builder = new ContextBuilder(DataVersion::fromVersionString($this->testVersion));
    }

    public function testNoExceptionIsThrownForIndividualContextCreationWithCodesWithoutMatchingDecorator()
    {
        $result = $this->builder->createContext(['nonExistingContextPartCode' => 'contextPartValue']);
        $this->assertInstanceOf(Context::class, $result);
    }

    public function testExceptionIsThrownForContextListCreationWithDataSetsContainingCodesWithoutMatchingDecorator()
    {
        $this->setExpectedException(ContextDecoratorNotFoundException::class);
        $this->builder->createContextsFromDataSets([['nonExistingContextPartCode' => 'contextPartValue']]);
    }

    public function testItDoesNotThrowExceptionForContextListCreationIfVersionIsPresent()
    {
        $result = $this->builder->createContextsFromDataSets([[VersionedContext::CODE => 'abc123']]);
        $this->assertContainsOnly(Context::class, $result);
    }

    public function testNoExceptionIsThrownForDataSetMissingRegisteredDecoratorParts()
    {
        $this->builder->registerContextDecorator('locale', LocaleContextDecorator::class);
        $contexts = [
            ['stub_valid_test' => 'dummy'],
        ];
        foreach ($this->builder->createContextsFromDataSets($contexts) as $context) {
            $this->assertNotContains('locale', $context->getSupportedCodes());
        }
    }

    public function testExceptionIsThrownForNonContextDecoratorClass()
    {
        $this->setExpectedException(InvalidContextDecoratorClassException::class);
        $this->builder->createContext(['stub_invalid_test' => 'dummy']);
    }

    public function testContextsForGivePartsIsReturned()
    {
        $contexts = [
            ['stub_valid_test' => 'dummy'],
        ];
        $result = $this->builder->createContextsFromDataSets($contexts);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Context::class, $result);
    }

    /**
     * @param string $testCode
     * @param string $expected
     * @dataProvider underscoreCodeDataProvider
     */
    public function testUnderscoresAreRemovesFromContextKey($testCode, $expected)
    {
        $method = new \ReflectionMethod($this->builder, 'removeUnderscores');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invoke($this->builder, $testCode));
    }

    /**
     * @return array[]
     */
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

    public function testExceptionIsThrownIfNonExistentDecoratorClassIsRegistered()
    {
        $this->setExpectedException(ContextDecoratorNotFoundException::class);
        $this->builder->registerContextDecorator('test', 'Non\\Existent\\DecoratorClass');
    }

    public function testExceptionIsThrownIfInvalidDecoratorClassIsAdded()
    {
        $this->setExpectedException(InvalidContextDecoratorClassException::class);
        $this->builder->registerContextDecorator('test', StubInvalidTestContextDecorator::class);
    }

    public function testContextCodesToClassesAreRegistered()
    {
        $this->builder->registerContextDecorator('test', StubValidTestContextDecorator::class);
        $contexts = [
            ['test' => 'dummy'],
        ];
        $result = $this->builder->createContextsFromDataSets($contexts);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Context::class, $result);
    }

    public function testContextIsCreatedFromARequest()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $result = $this->builder->createFromRequest($stubRequest);
        $this->assertInstanceOf(Context::class, $result);
    }
    
    public function testContextDecoratorsReceiveRequestAsPartOfSourceData()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $this->builder->registerContextDecorator('request_test', TestContextDecorator::class);
        
        /** @var TestContextDecorator $context */
        $context = $this->builder->createFromRequest($stubRequest);
        
        $rawSourceData = $context->getRawSourceDataForTest();
        $this->assertArrayHasKey('request', $rawSourceData);
        $this->assertSame($stubRequest, $rawSourceData['request']);
    }

    public function testContextDecoratorOrderIsIndependentOfTheContextSourceArrayOrder()
    {
        $contextSourceA = ['stub_valid_test' => 'dummy', 'website' => 'test'];
        $contextSourceB = ['website' => 'test', 'stub_valid_test' => 'dummy'];
        
        $contextA = $this->builder->createContext($contextSourceA);
        $contextB = $this->builder->createContext($contextSourceB);
        
        $this->assertSame((string) $contextA, (string) $contextB, 'Context decorator order depends on input');
    }

    public function testContextDecoratorOrderIsIndependentOfTheContextSourceArrayOrderInForDataSets()
    {
        $contextDataSetA = [['stub_valid_test' => 'dummy', 'website' => 'test']];
        $contextDataSetB = [['website' => 'test', 'stub_valid_test' => 'dummy']];
        
        $setA = $this->builder->createContextsFromDataSets($contextDataSetA);
        $setB = $this->builder->createContextsFromDataSets($contextDataSetB);

        $message = 'Context decorators in context sets are not always built in the same order';
        $this->assertSame((string) $setA[0], (string) $setB[0], $message);
    }

    public function testContextDecoratorOrderIsIndependentOfDecoratorRegistrationOrder()
    {
        $builderA = $this->builder;
        $builderB = new ContextBuilder(DataVersion::fromVersionString('1'));
        
        $builderA->registerContextDecorator('locale', LocaleContextDecorator::class);
        $builderA->registerContextDecorator('website', WebsiteContextDecorator::class);

        $builderB->registerContextDecorator('website', WebsiteContextDecorator::class);
        $builderB->registerContextDecorator('locale', LocaleContextDecorator::class);

        $contextSource = ['request' => $this->getMock(HttpRequest::class, [], [], '', false)];
        $contextA = $builderA->createContext($contextSource);
        $contextB = $builderB->createContext($contextSource);

        $message = 'Context decorator order depends on registration order';
        $this->assertSame($contextA->__toString(), $contextB->__toString(), $message);
    }

    public function testItUsesTheVersionSuppliedInTheDataSetIfPresent()
    {
        $contextVersion111 = $this->builder->createContext([VersionedContext::CODE => '111']);
        $contextVersion222 = $this->builder->createContext([VersionedContext::CODE => '222']);
        $this->assertSame('111', $contextVersion111->getValue(VersionedContext::CODE));
        $this->assertSame('222', $contextVersion222->getValue(VersionedContext::CODE));
    }

    public function testItUsesTheVersionInjectedInTheBuilderIfNotPresentInDataSet()
    {
        $context = $this->builder->createContext([]);
        $this->assertSame($this->testVersion, $context->getValue(VersionedContext::CODE));
    }

    public function testStaticFactoryMethodThrowsExceptionIfVersionIsMissingFromDataSet()
    {
        $this->setExpectedException(
            DataVersionMissingInContextDataSetException::class,
            'The data version has to be part of the data set when using the static context factory method.'
        );
        ContextBuilder::rehydrateContext([]);
    }

    public function testItReturnsAContextWithTheMatchingValues()
    {
        $dataSet = [
            WebsiteContextDecorator::CODE => 'test',
            LocaleContextDecorator::CODE => 'xx_XX',
            VersionedContext::CODE => '42'
        ];
        
        $context = ContextBuilder::rehydrateContext($dataSet);
        
        $this->assertInstanceOf(Context::class, $context);
        $this->assertSame('test', $context->getValue(WebsiteContextDecorator::CODE));
        $this->assertSame('xx_XX', $context->getValue(LocaleContextDecorator::CODE));
        $this->assertSame('42', (string) $context->getValue(VersionedContext::CODE));
    }
}
