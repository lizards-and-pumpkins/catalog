<?php

namespace LizardsAndPumpkins\Renderer\Translation;

use LizardsAndPumpkins\Renderer\ThemeLocator;
use LizardsAndPumpkins\Renderer\Translation\Exception\LocaleDirectoryNotReadableException;
use LizardsAndPumpkins\Renderer\Translation\Exception\MalformedTranslationFileException;
use LizardsAndPumpkins\Renderer\Translation\Exception\TranslationFileNotReadableException;

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
     * @return CsvTranslator
     */
    public static function forLocale($localeCode, ThemeLocator $themeLocator)
    {
        $translationFiles = self::getTranslationFilesFromLocaleDirectory($localeCode, $themeLocator);
        $translations = array_reduce($translationFiles, function (array $carry, $filePath) {
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
     * @param string $localeCode
     * @param ThemeLocator $themeLocator
     * @return string[]
     */
    private static function getTranslationFilesFromLocaleDirectory($localeCode, ThemeLocator $themeLocator)
    {
        chdir(__DIR__ . '/../../..');

        $localeDirectoryPath = $themeLocator->getThemeDirectory() . '/locale/' . $localeCode;

        if (!is_dir($localeDirectoryPath)) {
            return [];
        }

        self::validateLocaleDirectory($localeDirectoryPath);

        return glob($localeDirectoryPath . '/*.csv');
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
     * @param $localeDirectoryPath
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
}
