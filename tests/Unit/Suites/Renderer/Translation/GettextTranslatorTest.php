<?php

namespace LizardsAndPumpkins\Renderer\Translation;

use LizardsAndPumpkins\Renderer\ThemeLocator;
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
        $testThemeDirectoryPath = sys_get_temp_dir();
        $testTranslationFilePath = $testThemeDirectoryPath . '/locale/' . $this->testLocaleCode . '/LC_MESSAGES/' .
                                     $this->testLocaleCode . '.mo';
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
