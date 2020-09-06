<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Translation;

interface Translator extends \JsonSerializable
{
    public function translate(string $string): string;
}
