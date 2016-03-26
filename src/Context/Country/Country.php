<?php

namespace LizardsAndPumpkins\Context\Country;

use LizardsAndPumpkins\Context\Country\Exception\InvalidCountrySpecificationException;

class Country
{
    /**
     * @var string
     */
    private $twoCharIso3166Code;

    /**
     * @param string $isoString
     */
    private function __construct($isoString)
    {
        $this->twoCharIso3166Code = $isoString;
    }
    
    /**
     * @param string|Country $isoString
     * @return Country
     */
    public static function from2CharIso3166($isoString)
    {
        if ($isoString instanceof self) {
            return $isoString;
        }
        self::validateType($isoString);
        $trimmedIsoString = trim($isoString);
        self::validateCountrySpecFormat($trimmedIsoString);
        return new static(strtoupper($trimmedIsoString));
    }

    /**
     * @param string $isoString
     */
    private static function validateType($isoString)
    {
        if (!is_string($isoString)) {
            $message = sprintf('The country specification has to be a string, got "%s"', self::getType($isoString));
            throw new InvalidCountrySpecificationException($message);
        }
    }

    /**
     * @param string $isoString
     */
    private static function validateCountrySpecFormat($isoString)
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

    /**
     * @param mixed $variable
     * @return string
     */
    private static function getType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->twoCharIso3166Code;
    }

    /**
     * @param Country $otherCountry
     * @return bool
     */
    public function isEqualTo(Country $otherCountry)
    {
        return $this->twoCharIso3166Code === $otherCountry->twoCharIso3166Code;
    }
}
