<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Translation;

use LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator;
use LizardsAndPumpkins\Translation\Exception\LocaleDirectoryNotReadableException;
use LizardsAndPumpkins\Translation\Exception\MalformedTranslationFileException;
use LizardsAndPumpkins\Translation\Exception\TranslationFileNotReadableException;
use LizardsAndPumpkins\TestFileFixtureTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Translation\CsvTranslator
 */
class CsvTranslatorTest extends TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var string
     */
    private $testLocaleCode = 'foo_BAR';

    /**
     * @var ThemeLocator|MockObject
     */
    private $stubThemeLocator;

    final protected function setUp(): void
    {
        $this->stubThemeLocator = $this->createMock(ThemeLocator::class);
    }

    public function testTranslatorInterfaceIsImplemented(): void
    {
        $testThemeDirectoryPath = sys_get_temp_dir();
        $this->stubThemeLocator->method('getThemeDirectory')->willReturn($testThemeDirectoryPath);

        $testLocaleDirectoryPath = $testThemeDirectoryPath . '/locale/' . $this->testLocaleCode;
        $this->createFixtureDirectory($testLocaleDirectoryPath);

        $fileNames = [];

        $result = CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator, $fileNames);
        $this->assertInstanceOf(Translator::class, $result);
    }

    public function testExceptionIsThrownIfLocaleDirectoryIsNotReadable(): void
    {
        $this->expectException(LocaleDirectoryNotReadableException::class);

        $testThemeDirectoryPath = sys_get_temp_dir();
        $testLocaleDirectoryPath = $testThemeDirectoryPath . '/locale/' . $this->testLocaleCode;

        $this->createFixtureDirectory($testLocaleDirectoryPath);
        chmod($testLocaleDirectoryPath, 0000);

        $this->stubThemeLocator->method('getThemeDirectory')->willReturn($testThemeDirectoryPath);

        $fileNames = [];

        CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator, $fileNames);
    }

    public function testExceptionIsThrownIfTranslationFileIsNotReadable(): void
    {
        $this->expectException(TranslationFileNotReadableException::class);

        $testThemeDirectoryPath = sys_get_temp_dir();
        $testLocaleDirectoryPath = $testThemeDirectoryPath . '/locale/' . $this->testLocaleCode;
        $testTranslationFilePath = $testLocaleDirectoryPath . '/test_translation_file.csv';
        $testTranslationFileContents = '"foo","bar"';

        $this->createFixtureFile($testTranslationFilePath, $testTranslationFileContents, 0000);
        $this->stubThemeLocator->method('getThemeDirectory')->willReturn($testThemeDirectoryPath);

        $fileNames = ['test_translation_file.csv'];

        CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator, $fileNames);
    }

    public function testExceptionIsThrownIfTranslationFileHasWrongFormatting(): void
    {
        $this->expectException(MalformedTranslationFileException::class);

        $testThemeDirectoryPath = sys_get_temp_dir();
        $testLocaleDirectoryPath = $testThemeDirectoryPath . '/locale/' . $this->testLocaleCode;
        $testTranslationFilePath = $testLocaleDirectoryPath . '/test_translation_file.csv';
        $testTranslationFileContents = '"foo,bar"';

        $this->createFixtureFile($testTranslationFilePath, $testTranslationFileContents);
        $this->stubThemeLocator->method('getThemeDirectory')->willReturn($testThemeDirectoryPath);

        $fileNames = ['test_translation_file.csv'];

        CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator, $fileNames);
    }

    public function testOriginalStringIsReturnedIfTranslationDirectoryDoesNotExist(): void
    {
        $fileNames = [];

        $translator = CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator, $fileNames);

        $testTranslationSource = 'foo';
        $result = $translator->translate($testTranslationSource);

        $this->assertSame($testTranslationSource, $result);
    }

    public function testOriginalStringIsReturnedIfTranslationIsMissing(): void
    {
        $testThemeDirectoryPath = sys_get_temp_dir();
        $testLocaleDirectoryPath = $testThemeDirectoryPath . '/locale/' . $this->testLocaleCode;

        $this->createFixtureDirectory($testLocaleDirectoryPath);

        $this->stubThemeLocator->method('getThemeDirectory')->willReturn($testThemeDirectoryPath);

        $fileNames = [];

        $translator = CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator, $fileNames);

        $testTranslationSource = 'foo';
        $result = $translator->translate($testTranslationSource);

        $this->assertSame($testTranslationSource, $result);
    }

    public function testGivenStringIsTranslated(): void
    {
        $testThemeDirectoryPath = sys_get_temp_dir();
        $testLocaleDirectoryPath = $testThemeDirectoryPath . '/locale/' . $this->testLocaleCode;
        $testTranslationFilePath = $testLocaleDirectoryPath . '/test_translation_file.csv';

        $testTranslationSource = 'foo';
        $testTranslationResult = 'bar';

        $testTranslationFileContents = sprintf('"%s","%s"', $testTranslationSource, $testTranslationResult);

        $this->createFixtureFile($testTranslationFilePath, $testTranslationFileContents);
        $this->stubThemeLocator->method('getThemeDirectory')->willReturn($testThemeDirectoryPath);

        $fileNames = ['test_translation_file.csv'];

        $translator = CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator, $fileNames);
        $result = $translator->translate($testTranslationSource);

        $this->assertSame($testTranslationResult, $result);
    }

    public function testGivenStringIsNotTranslatedIfTranslationFileIsNotSpecifiedEvenIfExists(): void
    {
        $testThemeDirectoryPath = sys_get_temp_dir();
        $testLocaleDirectoryPath = $testThemeDirectoryPath . '/locale/' . $this->testLocaleCode;
        $testTranslationFilePath = $testLocaleDirectoryPath . '/test_translation_file.csv';

        $testTranslationSource = 'foo';
        $testTranslationResult = 'bar';

        $testTranslationFileContents = sprintf('"%s","%s"', $testTranslationSource, $testTranslationResult);

        $this->createFixtureFile($testTranslationFilePath, $testTranslationFileContents);
        $this->stubThemeLocator->method('getThemeDirectory')->willReturn($testThemeDirectoryPath);

        $fileNames = [];

        $translator = CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator, $fileNames);
        $result = $translator->translate($testTranslationSource);

        $this->assertSame($testTranslationSource, $result);
    }

    public function testAllTranslationsAreReturnedAsAnArray(): void
    {
        $testThemeDirectoryPath = sys_get_temp_dir();
        $testLocaleDirectoryPath = $testThemeDirectoryPath . '/locale/' . $this->testLocaleCode;
        $testTranslationFilePath = $testLocaleDirectoryPath . '/test_translation_file.csv';

        $translationSourceA = 'foo';
        $translationResultA = 'bar';
        $translationA = sprintf('"%s","%s"', $translationSourceA, $translationResultA);

        $translationSourceB = 'baz';
        $translationResultB = 'qux';
        $translationB = sprintf('"%s","%s"', $translationSourceB, $translationResultB);

        $testTranslationFileContents = $translationA . "\n" . $translationB;
        $this->createFixtureFile($testTranslationFilePath, $testTranslationFileContents);
        $this->stubThemeLocator->method('getThemeDirectory')->willReturn($testThemeDirectoryPath);

        $fileNames = ['test_translation_file.csv'];

        $translator = CsvTranslator::forLocale($this->testLocaleCode, $this->stubThemeLocator, $fileNames);

        $result = $translator->jsonSerialize();
        $expectedArray = [$translationSourceA => $translationResultA, $translationSourceB => $translationResultB];

        $this->assertSame($expectedArray, $result);
    }
}
