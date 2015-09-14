<?php

namespace Brera\Renderer\Translation;

use Brera\Renderer\ThemeLocator;
use Brera\Renderer\Translation\Exception\MalformedTranslationFileException;
use Brera\Renderer\Translation\Exception\TranslationFileNotReadableException;

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
     * @param string $locale
     * @param ThemeLocator $themeLocator
     * @return CsvTranslator
     */
    public static function forLocale($locale, ThemeLocator $themeLocator)
    {
        $localeDirectoryPath = $themeLocator->getLocaleDirectoryPath($locale);
        $translations = [];

        if (is_dir($localeDirectoryPath) && is_readable($localeDirectoryPath)) {
            $translationFiles = glob($localeDirectoryPath . '/*.csv');
            foreach ($translationFiles as $filePath) {
                self::validateTranslationFileIsReadable($filePath);

                $fileRows = file($filePath);

                foreach ($fileRows as $row) {
                    $data = str_getcsv($row);

                    if (2 !== count($data)) {
                        throw new MalformedTranslationFileException(
                            sprintf('Bad translation formatting in "%s": %s', $filePath, $row)
                        );
                    }

                    $translations[$data[0]] = $data[1];
                }
            }
        }

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
}
