<?php

namespace Brera\Renderer\Translation;

use Brera\Renderer\ThemeLocator;
use Brera\Renderer\Translation\Exception\MalformedTranslationFileException;
use Brera\Renderer\Translation\Exception\TranslationFileNotReadableException;
use Brera\TestFileFixtureTrait;

/**
 * @covers \Brera\Renderer\Translation\CsvTranslator
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
        $result = CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator);
        $this->assertInstanceOf(Translator::class, $result);
    }

    public function testExceptionIsThrownIfTranslationFileIsNotReadable()
    {
        $this->setExpectedException(TranslationFileNotReadableException::class);

        $testLocaleDirectoryPath = sys_get_temp_dir() . '/' . $this->testLocaleCode;
        $testTranslationFilePath = $testLocaleDirectoryPath . '/test_translation_file.csv';
        $testTranslationFileContents = '"foo","bar"';

        $this->createFixtureFile($testTranslationFilePath, $testTranslationFileContents, 0000);
        $this->stubThemeLocator->method('getLocaleDirectoryPath')->willReturn($testLocaleDirectoryPath);

        CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator);
    }

    public function testExceptionIsThrownIfTranslationFileHasWrongFormatting()
    {
        $this->setExpectedException(MalformedTranslationFileException::class);

        $testLocaleDirectoryPath = sys_get_temp_dir() . '/' . $this->testLocaleCode;
        $testTranslationFilePath = $testLocaleDirectoryPath . '/test_translation_file.csv';
        $testTranslationFileContents = '"foo,bar"';

        $this->createFixtureFile($testTranslationFilePath, $testTranslationFileContents);
        $this->stubThemeLocator->method('getLocaleDirectoryPath')->willReturn($testLocaleDirectoryPath);

        CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator);
    }

    public function testOriginalStringIsReturnedIfTranslationIsMissing()
    {
        $testTranslationSource = 'foo';
        $translator = CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator);
        $result = $translator->translate($testTranslationSource);

        $this->assertSame($testTranslationSource, $result);
    }

    public function testGivenStringIsTranslated()
    {
        $testLocaleDirectoryPath = sys_get_temp_dir() . '/' . $this->testLocaleCode;
        $testTranslationFilePath = $testLocaleDirectoryPath . '/test_translation_file.csv';

        $testTranslationSource = 'foo';
        $testTranslationResult = 'bar';

        $testTranslationFileContents = sprintf('"%s","%s"', $testTranslationSource, $testTranslationResult);

        $this->createFixtureFile($testTranslationFilePath, $testTranslationFileContents);
        $this->stubThemeLocator->method('getLocaleDirectoryPath')->willReturn($testLocaleDirectoryPath);

        $translator = CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator);
        $result = $translator->translate($testTranslationSource);

        $this->assertSame($testTranslationResult, $result);
    }
}
