<?php

namespace LizardsAndPumpkins\Translation;

interface Translator extends \JsonSerializable
{
    /**
     * @param string $string
     * @return string
     */
    public function translate($string);
}
