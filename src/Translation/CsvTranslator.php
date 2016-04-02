<?php

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
    public static function forLocale($localeCode, ThemeLocator $themeLocator, array $fileNames)
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

    /**
     * @param string $string
     * @return string
     */
    public function translate($string)
    {
        if (!isset($this->translations[$string])) {
            return $string;
        }

        return $this->translations[$string];
    }

    /**
     * @param string $translationFilePath
     */
    private static function validateTranslationFileIsReadable($translationFilePath)
    {
        if (!is_readable($translationFilePath)) {
            throw new TranslationFileNotReadableException(
                sprintf('Translation file "%s" is not readable', $translationFilePath)
            );
        }
    }

    /**
     * @param string $localeDirectoryPath
     */
    private static function validateLocaleDirectory($localeDirectoryPath)
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
    private static function validateTranslation(array $translationData, $filePath, $row)
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
    function jsonSerialize()
    {
        return $this->translations;
    }
}
