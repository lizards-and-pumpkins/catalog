<?php

namespace LizardsAndPumpkins\Context\Locale;

use LizardsAndPumpkins\Context\Locale\Exception\InvalidLocaleSpecificationException;

class Locale
{
    const CONTEXT_CODE = 'locale';

    private $localeCode;

    /**
     * @param string $localeCode
     */
    public function __construct($localeCode)
    {
        $this->localeCode = $localeCode;
    }

    /**
     * @param string $localeCode
     * @return Locale
     */
    public static function fromCodeString($localeCode)
    {
        if (!is_string($localeCode)) {
            throw new InvalidLocaleSpecificationException(
                sprintf('The country specification has to be a string, got "%s".', gettype($localeCode))
            );
        }
        
        return new self($localeCode);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->localeCode;
    }
}
