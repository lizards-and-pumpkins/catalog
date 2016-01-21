<?php

namespace LizardsAndPumpkins\Renderer\Translation;

use LizardsAndPumpkins\Renderer\Translation\Exception\UndefinedTranslatorException;

class TranslatorRegistry
{
    /**
     * @var callable[]
     */
    private $translatorFactories;

    /**
     * @var Translator[]
     */
    private $translators = [];

    /**
     * @param string $pageCode
     * @param callable $translatorFactory
     */
    public function register($pageCode, callable $translatorFactory)
    {
        $this->translatorFactories[$pageCode] = $translatorFactory;
    }

    /**
     * @param string $pageCode
     * @param string $locale
     * @return Translator
     */
    public function getTranslator($pageCode, $locale)
    {
        if (! isset($this->translatorFactories[$pageCode])) {
            throw new UndefinedTranslatorException(sprintf('No translator found for page "%s".', $pageCode));
        }

        if (! isset($this->translators[$pageCode][$locale])) {
            $this->translators[$pageCode][$locale] = call_user_func($this->translatorFactories[$pageCode], $locale);
        }

        return $this->translators[$pageCode][$locale];
    }
}
