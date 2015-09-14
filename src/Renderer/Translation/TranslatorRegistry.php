<?php

namespace Brera\Renderer\Translation;

use Brera\Renderer\ThemeLocator;

class TranslatorRegistry
{
    /**
     * @var string
     */
    private $translatorClassName;

    /**
     * @var ThemeLocator
     */
    private $themeLocator;

    /**
     * @var Translator[]
     */
    private $translators = [];

    /**
     * @param string $translatorClassName
     * @param ThemeLocator $themeLocator
     */
    public function __construct($translatorClassName, ThemeLocator $themeLocator)
    {
        $this->translatorClassName = $translatorClassName;
        $this->themeLocator = $themeLocator;
    }

    /**
     * @param string $locale
     * @return Translator
     */
    public function getTranslatorForLocale($locale)
    {
        if (!isset($this->translators[$locale])) {
            $this->translators[$locale] = call_user_func(
                [$this->translatorClassName, 'forLocale'],
                $locale,
                $this->themeLocator
            );
        }

        return $this->translators[$locale];
    }
}
