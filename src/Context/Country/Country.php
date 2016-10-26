<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Country;

use LizardsAndPumpkins\Context\Country\Exception\InvalidCountrySpecificationException;

class Country
{
    const CONTEXT_CODE = 'country';
    
    /**
     * @var string
     */
    private $twoCharIso3166Code;

    private function __construct(string $isoString)
    {
        $this->twoCharIso3166Code = $isoString;
    }

    /**
     * @param string|Country $isoString
     * @return Country
     */
    public static function from2CharIso3166($isoString) : Country
    {
        if ($isoString instanceof self) {
            return $isoString;
        }

        $trimmedIsoString = trim($isoString);
        self::validateCountrySpecFormat($trimmedIsoString);

        return new static(strtoupper($trimmedIsoString));
    }

    private static function validateCountrySpecFormat(string $isoString)
    {
        if ('' === $isoString) {
            throw new InvalidCountrySpecificationException('The country specification must not be empty');
        }

        if (strlen($isoString) !== 2) {
            $message = sprintf('Two character string country specification expected (ISO 3166), got "%s"', $isoString);
            throw new InvalidCountrySpecificationException($message);
        }

        if (! preg_match('/^[a-zA-Z]+$/', $isoString)) {
            $invalidCharsMessageTemplate = 'The country specification may only contain characters from a-z, got "%s"';
            $invalidCharsErrorMessage = sprintf($invalidCharsMessageTemplate, $isoString);
            throw new InvalidCountrySpecificationException($invalidCharsErrorMessage);
        }
    }

    public function __toString() : string
    {
        return $this->twoCharIso3166Code;
    }

    public function isEqualTo(Country $otherCountry) : bool
    {
        return $this->twoCharIso3166Code === $otherCountry->twoCharIso3166Code;
    }
}
