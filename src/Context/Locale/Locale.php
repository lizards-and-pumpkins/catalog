<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Locale;

class Locale
{
    const CONTEXT_CODE = 'locale';

    private $localeCode;

    public function __construct(string $localeCode)
    {
        $this->localeCode = $localeCode;
    }

    public function __toString() : string
    {
        return $this->localeCode;
    }
}
