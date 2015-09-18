<?php

namespace LizardsAndPumpkins\Renderer\Translation;

use LizardsAndPumpkins\Renderer\ThemeLocator;
use LizardsAndPumpkins\Renderer\Translation\Exception\LocaleNotSupportedException;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Renderer\Translation\GettextTranslator
 */
class GettextTranslatorTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var string
     */
    private $testLocaleCode = 'en_US';

    /**
     * @var string
     */
    private $originalLocaleCode;

    /**
     * @var ThemeLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubThemeLocator;

    /**
     * @return string
     */
    private function getCurrentLocaleCode()
    {
        return setlocale(LC_ALL, 0);
    }

    protected function setUp()
    {
        if (!function_exists('gettext')) {
            $this->markTestSkipped('Gettext is not installed.');
        }

        $this->stubThemeLocator = $this->getMock(ThemeLocator::class, [], [], '', false);

        $this->originalLocaleCode = $this->getCurrentLocaleCode();
        if ($this->originalLocaleCode === $this->testLocaleCode) {
            $this->fail('Test can not be executed because original system locale is identical to test locale.');
        }
    }

    public function testTranslatorInterfaceIsImplemented()
    {
        $result = GettextTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator);
        $this->assertInstanceOf(Translator::class, $result);
    }

    public function testTranslatorInstantiationIsNotChangingOriginalLocale()
    {
        GettextTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator);
        $currentLocaleCode = $this->getCurrentLocaleCode();

        $this->assertSame($currentLocaleCode, $this->originalLocaleCode);
    }

    public function testExceptionIsThrownIfLocaleIsNotInstalled()
    {
        $this->setExpectedException(LocaleNotSupportedException::class);
        GettextTranslator::forLocale('foo_BAR', $this->stubThemeLocator);
    }

    public function testTranslationOfAStringIsNotChangingSystemLocale()
    {
        GettextTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator)->translate('foo');
        $currentLocaleCode = $this->getCurrentLocaleCode();

        $this->assertSame($currentLocaleCode, $this->originalLocaleCode);
    }

    public function testOriginalStringIsReturnedIfTranslationIsMissing()
    {
        $testTranslationSource = 'foo';
        $translator = GettextTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator);
        $result = $translator->translate($testTranslationSource);

        $this->assertSame($testTranslationSource, $result);
    }

    public function testGivenStringIsTranslated()
    {
        if (empty(shell_exec('which msgfmt'))) {
            $this->markTestSkipped('msgfmt is not installed.');
        }

        $testThemeDirectoryPath = sys_get_temp_dir();
        $testTranslationFilePath = sprintf(
            '%1$s/locale/%2$s/LC_MESSAGES/%2$s.mo',
            $testThemeDirectoryPath,
            $this->testLocaleCode
        );
        $this->createFixtureFile($testTranslationFilePath, '', 0777);

        $testTranslationSource = 'foo';
        $testTranslationResult = 'bar';

        shell_exec(sprintf(
            'printf "msgid \"%s\"\nmsgstr \"%s\"" | msgfmt - -o %s',
            $testTranslationSource,
            $testTranslationResult,
            $testTranslationFilePath
        ));

        $this->stubThemeLocator->method('getThemeDirectory')->willReturn($testThemeDirectoryPath);

        $translator = GettextTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator);
        $result = $translator->translate($testTranslationSource);

        $this->assertSame($testTranslationResult, $result);
    }
}
