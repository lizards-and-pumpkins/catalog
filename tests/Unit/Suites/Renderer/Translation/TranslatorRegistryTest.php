<?php

namespace LizardsAndPumpkins\Renderer\Translation;

/**
 * @covers \LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry
 */
class TranslatorRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TranslatorRegistry
     */
    private $registry;

    protected function setUp()
    {
        /** @var callable|\PHPUnit_Framework_MockObject_MockObject $stubTranslatorFactory */
        $stubTranslatorFactory = $this->getMockBuilder(Callback::class)->setMethods(['__invoke'])->getMock();
        $stubTranslatorFactory->method('__invoke')->willReturnCallback(function () {
            return $this->getMock(Translator::class);
        });

        $this->registry = new TranslatorRegistry($stubTranslatorFactory);
    }

    public function testTranslatorIsReturnedEvenIfLocaleIsNotAvailable()
    {
        $this->assertInstanceOf(Translator::class, $this->registry->getTranslatorForLocale('foo_BAR'));
    }

    public function testSameInstanceOfTranslatorIsReturnedOnConsecutiveCallsForSameLocale()
    {
        $instanceA = $this->registry->getTranslatorForLocale('foo_BAR');
        $instanceB = $this->registry->getTranslatorForLocale('foo_BAR');

        $this->assertSame($instanceA, $instanceB);
    }

    public function testDifferentInstancesOfTranslatorAreReturnedForDifferentLocales()
    {
        $instanceA = $this->registry->getTranslatorForLocale('foo_BAR');
        $instanceB = $this->registry->getTranslatorForLocale('baz_QUX');

        $this->assertNotSame($instanceA, $instanceB);
    }
}
