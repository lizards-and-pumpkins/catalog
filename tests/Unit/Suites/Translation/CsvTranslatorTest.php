<?php

namespace Brera\Translation;

use Brera\TestFileFixtureTrait;
use Brera\Translation\Exception\MalformedTranslationFileException;
use Brera\Translation\Exception\TranslationFileNotReadableException;

/**
 * @covers \Brera\Translation\CsvTranslator
 */
class CsvTranslatorTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var CsvTranslator
     */
    private $translator;

    protected function setUp()
    {

        $this->translator = new CsvTranslator;
    }

    public function testTranslatorInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Translator::class, $this->translator);
    }

    public function testExceptionIsThrownIfTranslationFileIsNotReadable()
    {
        $this->setExpectedException(TranslationFileNotReadableException::class);
        $this->translator->addFile('some-non-existing-file.csv');
    }

    public function testExceptionIsThrownIfTranslationFileHasWrongFormatting()
    {
        $this->setExpectedException(MalformedTranslationFileException::class);

        $testTranslationFilePath = sys_get_temp_dir() . '/test_translation_file.csv';
        $testTranslationFileContents = '"foo,bar"';
        $this->createFixtureFile($testTranslationFilePath, $testTranslationFileContents);

        $this->translator->addFile($testTranslationFilePath);

    }

    public function testOriginalStringIsReturnedIfTranslationIsMissing()
    {
        $testTranslationSource = 'foo';
        $result = $this->translator->translate($testTranslationSource);

        $this->assertSame($testTranslationSource, $result);
    }

    public function testGivenStringIsTranslated()
    {
        $testTranslationSource = 'foo';
        $testTranslationResult = 'bar';

        $testTranslationFilePath = sys_get_temp_dir() . '/test_translation_file.csv';
        $testTranslationFileContents = sprintf('"%s","%s"', $testTranslationSource, $testTranslationResult);
        $this->createFixtureFile($testTranslationFilePath, $testTranslationFileContents);

        $this->translator->addFile($testTranslationFilePath);
        $result = $this->translator->translate($testTranslationSource);

        $this->assertSame($testTranslationResult, $result);
    }
}
