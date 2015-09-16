<?php

namespace LizardsAndPumpkins\Renderer\Translation;

use LizardsAndPumpkins\Renderer\ThemeLocator;

class GettextTranslator implements Translator
{
    /**
     * @var string
     */
    private $localeCode;

    /**
     * @param string $localeCode
     */
    private function __construct($localeCode)
    {
        $this->localeCode = $localeCode;
    }

    /**
     * @param string $localeCode
     * @param ThemeLocator $themeLocator
     * @return GettextTranslator
     */
    public static function forLocale($localeCode, ThemeLocator $themeLocator)
    {
        self::validateLocale($localeCode);

        $localeDirectoryPath = $themeLocator->getThemeDirectory() . '/locale/' . $localeCode;
        bindtextdomain($localeCode, dirname($localeDirectoryPath));

        return new self($localeCode);
    }

    /**
     * @param string $string
     * @return string
     */
    public function translate($string)
    {
        setlocale(LC_ALL, $this->localeCode);
        return dgettext($this->localeCode, $string);
    }

    /**
     * @param string $localeCode
     */
    private static function validateLocale($localeCode)
    {
        if (false === setlocale(LC_ALL, $localeCode)) {
            throw new LocaleNotSupportedException(sprintf('Locale "%s" is not installed in the system.', $localeCode));
        }
    }
}
