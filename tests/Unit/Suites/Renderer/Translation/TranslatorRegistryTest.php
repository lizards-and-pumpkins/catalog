<?php

namespace Brera\Renderer\Translation;

use Brera\Renderer\ThemeLocator;

/**
 * @covers \Brera\Renderer\Translation\TranslatorRegistry
 * @uses   \Brera\Renderer\Translation\CsvTranslator
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
