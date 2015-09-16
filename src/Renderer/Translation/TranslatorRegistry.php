<?php

namespace LizardsAndPumpkins\Renderer\Translation;

class TranslatorRegistry
{
    /**
     * @var callable
     */
    private $translatorFactory;

    /**
     * @var Translator[]
     */
    private $translators = [];

    public function __construct(callable $translatorFactory)
    {
        $this->translatorFactory = $translatorFactory;
    }

    /**
     * @param string $locale
     * @return Translator
     */
    public function getTranslatorForLocale($locale)
    {
        if (!isset($this->translators[$locale])) {
            $this->translators[$locale] = call_user_func($this->translatorFactory, $locale);
        }

        return $this->translators[$locale];
    }
}
