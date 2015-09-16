<?php

namespace LizardsAndPumpkins\Renderer\Translation;

/**
 * @covers \LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Renderer\Translation\CsvTranslator
 */
class TranslatorRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TranslatorRegistry
     */
    private $registry;

    protected function setUp()
    {
        $stubTranslator = $this->getMock(Translator::class);

        /** @var callable|\PHPUnit_Framework_MockObject_MockObject $stubTranslatorFactory */
        $stubTranslatorFactory = $this->getMock(Callback::class, ['__invoke']);
        $stubTranslatorFactory->method('__invoke')->willReturn($stubTranslator);

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
}
