<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Translation;

use LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator;
use LizardsAndPumpkins\Translation\Exception\LocaleDirectoryNotReadableException;
use LizardsAndPumpkins\Translation\Exception\MalformedTranslationFileException;
use LizardsAndPumpkins\Translation\Exception\TranslationFileNotReadableException;

class CsvTranslator implements Translator
{
    /**
     * @var string[]
     */
    private $translations;

    /**
     * @param string[] $translations
     */
    private function __construct(array $translations)
    {
        $this->translations = $translations;
    }

    /**
     * @param string $localeCode
     * @param ThemeLocator $themeLocator
     * @param string[] $fileNames
     * @return CsvTranslator
     */
    public static function forLocale(string $localeCode, ThemeLocator $themeLocator, array $fileNames) : CsvTranslator
    {
        $localeDirectoryPath = $themeLocator->getThemeDirectory() . '/locale/' . $localeCode . '/';

        if (!is_dir($localeDirectoryPath)) {
            return new self([]);
        }

        self::validateLocaleDirectory($localeDirectoryPath);

        $translations = array_reduce($fileNames, function (array $carry, $fileName) use ($localeDirectoryPath) {
            $filePath = $localeDirectoryPath . $fileName;
            self::validateTranslationFileIsReadable($filePath);
            $fileRows = file($filePath);

            foreach ($fileRows as $row) {
                $data = str_getcsv($row);
                self::validateTranslation($data, $filePath, $row);
                $carry[$data[0]] = $data[1];
            }

            return $carry;
        }, []);

        return new self($translations);
    }

    public function translate(string $string) : string
    {
        if (!isset($this->translations[$string])) {
            return $string;
        }

        return $this->translations[$string];
    }

    private static function validateTranslationFileIsReadable(string $translationFilePath)
    {
        if (!is_readable($translationFilePath)) {
            throw new TranslationFileNotReadableException(
                sprintf('Translation file "%s" is not readable', $translationFilePath)
            );
        }
    }

    private static function validateLocaleDirectory(string $localeDirectoryPath)
    {
        if (!is_readable($localeDirectoryPath)) {
            throw new LocaleDirectoryNotReadableException(
                sprintf('Locale directory "%s" is not readable', $localeDirectoryPath)
            );
        }
    }

    /**
     * @param string[] $translationData
     * @param string $filePath
     * @param string $row
     */
    private static function validateTranslation(array $translationData, string $filePath, string $row)
    {
        if (2 !== count($translationData)) {
            throw new MalformedTranslationFileException(
                sprintf('Bad translation formatting in "%s": %s', $filePath, $row)
            );
        }
    }

    /**
     * @return string[]
     */
    public function jsonSerialize() : array
    {
        return $this->translations;
    }
}
