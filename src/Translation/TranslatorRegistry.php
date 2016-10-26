<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Translation;

use LizardsAndPumpkins\Translation\Exception\UndefinedTranslatorException;

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

    public function register(string $pageCode, callable $translatorFactory)
    {
        $this->translatorFactories[$pageCode] = $translatorFactory;
    }

    public function getTranslator(string $pageCode, string $locale) : Translator
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
