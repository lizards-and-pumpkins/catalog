<?php

namespace LizardsAndPumpkins\Renderer\Translation;

use LizardsAndPumpkins\Renderer\ThemeLocator;
use LizardsAndPumpkins\Renderer\Translation\Exception\MalformedTranslationFileException;
use LizardsAndPumpkins\Renderer\Translation\Exception\TranslationFileNotReadableException;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Renderer\Translation\CsvTranslator
 */
class CsvTranslatorTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var string
     */
    private $testLocaleCode = 'foo_BAR';

    /**
     * @var ThemeLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubThemeLocator;

    protected function setUp()
    {
        $this->stubThemeLocator = $this->getMock(ThemeLocator::class, [], [], '', false);
    }

    public function testTranslatorInterfaceIsImplemented()
    {
        $testThemeDirectoryPath = sys_get_temp_dir();
        $this->stubThemeLocator->method('getThemeDirectory')->willReturn($testThemeDirectoryPath);

        $testLocaleDirectoryPath = $testThemeDirectoryPath . '/locale/' . $this->testLocaleCode;
        $this->createFixtureDirectory($testLocaleDirectoryPath);

        $result = CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator);
        $this->assertInstanceOf(Translator::class, $result);
    }

    public function testExceptionIsThrownIfLocaleDirectoryIsNotReadable()
    {
        $this->setExpectedException(LocaleDirectoryNotReadableException::class);

        $testThemeDirectoryPath = sys_get_temp_dir();
        $testLocaleDirectoryPath = $testThemeDirectoryPath . '/locale/' . $this->testLocaleCode;

        $this->createFixtureDirectory($testLocaleDirectoryPath);
        chmod($testLocaleDirectoryPath, 0000);

        $this->stubThemeLocator->method('getThemeDirectory')->willReturn($testThemeDirectoryPath);

        CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator);
    }

    public function testExceptionIsThrownIfTranslationFileIsNotReadable()
    {
        $this->setExpectedException(TranslationFileNotReadableException::class);

        $testThemeDirectoryPath = sys_get_temp_dir();
        $testLocaleDirectoryPath = $testThemeDirectoryPath . '/locale/' . $this->testLocaleCode;
        $testTranslationFilePath = $testLocaleDirectoryPath . '/test_translation_file.csv';
        $testTranslationFileContents = '"foo","bar"';

        $this->createFixtureFile($testTranslationFilePath, $testTranslationFileContents, 0000);
        $this->stubThemeLocator->method('getThemeDirectory')->willReturn($testThemeDirectoryPath);

        CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator);
    }

    public function testExceptionIsThrownIfTranslationFileHasWrongFormatting()
    {
        $this->setExpectedException(MalformedTranslationFileException::class);

        $testThemeDirectoryPath = sys_get_temp_dir();
        $testLocaleDirectoryPath = $testThemeDirectoryPath . '/locale/' . $this->testLocaleCode;
        $testTranslationFilePath = $testLocaleDirectoryPath . '/test_translation_file.csv';
        $testTranslationFileContents = '"foo,bar"';

        $this->createFixtureFile($testTranslationFilePath, $testTranslationFileContents);
        $this->stubThemeLocator->method('getThemeDirectory')->willReturn($testThemeDirectoryPath);

        CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator);
    }

    public function testOriginalStringIsReturnedIfTranslationIsMissing()
    {
        $testThemeDirectoryPath = sys_get_temp_dir();
        $this->stubThemeLocator->method('getThemeDirectory')->willReturn($testThemeDirectoryPath);

        $testLocaleDirectoryPath = $testThemeDirectoryPath . '/locale/' . $this->testLocaleCode;
        $this->createFixtureDirectory($testLocaleDirectoryPath);

        $testTranslationSource = 'foo';
        $translator = CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator);
        $result = $translator->translate($testTranslationSource);

        $this->assertSame($testTranslationSource, $result);
    }

    public function testGivenStringIsTranslated()
    {
        $testThemeDirectoryPath = sys_get_temp_dir();
        $testLocaleDirectoryPath = $testThemeDirectoryPath . '/locale/' . $this->testLocaleCode;
        $testTranslationFilePath = $testLocaleDirectoryPath . '/test_translation_file.csv';

        $testTranslationSource = 'foo';
        $testTranslationResult = 'bar';

        $testTranslationFileContents = sprintf('"%s","%s"', $testTranslationSource, $testTranslationResult);

        $this->createFixtureFile($testTranslationFilePath, $testTranslationFileContents);
        $this->stubThemeLocator->method('getThemeDirectory')->willReturn($testThemeDirectoryPath);

        $translator = CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator);
        $result = $translator->translate($testTranslationSource);

        $this->assertSame($testTranslationResult, $result);
    }
}
