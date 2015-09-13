<?php

namespace Brera\Renderer\Translation;

use Brera\Renderer\Translation\Exception\MalformedTranslationFileException;
use Brera\Renderer\Translation\Exception\TranslationFileNotReadableException;

class CsvTranslator implements Translator
{
    /**
     * @var string[]
     */
    private $translations = [];

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

    public function addFile($translationFilePath)
    {
        $this->validateTranslationFileIsReadable($translationFilePath);

        $fileRows = file($translationFilePath);

        foreach ($fileRows as $row) {
            $data = str_getcsv($row);

            if (2 !== count($data)) {
                throw new MalformedTranslationFileException(
                    sprintf('Bad translation formatting in "%s": %s', $translationFilePath, $row)
                );
            }

            $this->translations[$data[0]] = $data[1];
        }
    }

    /**
     * @param string $translationFilePath
     */
    private function validateTranslationFileIsReadable($translationFilePath)
    {
        if (!is_readable($translationFilePath)) {
            throw new TranslationFileNotReadableException(
                sprintf('Translation file "%s" is not readable', $translationFilePath)
            );
        }
    }
}
