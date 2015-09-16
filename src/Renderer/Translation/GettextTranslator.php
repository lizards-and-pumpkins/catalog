<?php

namespace LizardsAndPumpkins\Renderer\Translation;

use LizardsAndPumpkins\Renderer\ThemeLocator;
use LizardsAndPumpkins\Renderer\Translation\Exception\LocaleNotSupportedException;

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
        $originalLocaleCode = self::getCurrentLocaleCode();
        self::setLocale($this->localeCode);
        $translation = dgettext($this->localeCode, $string);
        self::setLocale($originalLocaleCode);

        return $translation;
    }

    /**
     * @param string $localeCode
     */
    private static function validateLocale($localeCode)
    {
        $originalLocaleCode = self::getCurrentLocaleCode();
        $newLocaleCode = self::setLocale($localeCode);
        self::setLocale($originalLocaleCode);

        if (false === $newLocaleCode) {
            throw new LocaleNotSupportedException(sprintf('Locale "%s" is not installed in the system.', $localeCode));
        }
    }

    /**
     * @return string
     */
    private static function getCurrentLocaleCode()
    {
        return setlocale(LC_ALL, 0);
    }

    /**
     * @param string $localeCode
     * @return string
     */
    private static function setLocale($localeCode)
    {
        return setlocale(LC_ALL, $localeCode);
    }
}
