<?php

namespace LizardsAndPumpkins\Renderer\Translation;

use LizardsAndPumpkins\Renderer\ThemeLocator;

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
        /** @var ThemeLocator|\PHPUnit_Framework_MockObject_MockObject $stubThemeLocator */
        $stubThemeLocator = $this->getMock(ThemeLocator::class, [], [], '', false);
        $this->registry = new TranslatorRegistry(CsvTranslator::class, $stubThemeLocator);
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
