<?php

namespace Brera\Renderer\Translation;

class NullTranslator implements Translator
{
    /**
     * @param string $string
     * @return string
     */
    public function translate($string)
    {
        return $string;
    }
}
