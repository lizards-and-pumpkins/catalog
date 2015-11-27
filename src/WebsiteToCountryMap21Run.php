<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\InvalidWebsiteCodeException;

class WebsiteToCountryMap21Run implements WebsiteToCountryMap
{
    private $defaultCountry = 'DE';

    private $map = [
        'ru' => 'DE',
        'fr' => 'FR',
    ];

    /**
     * @param string $websiteCode
     * @return string
     */
    public function getCountry($websiteCode)
    {
        $this->validateWebsiteCode($websiteCode);
        return isset($this->map[$websiteCode]) ?
            $this->map[$websiteCode] :
            $this->defaultCountry;
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private function getVariableType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }

    /**
     * @param mixed $websiteCode
     */
    private function validateWebsiteCode($websiteCode)
    {
        $this->validateWebsiteIsString($websiteCode);
        $this->validateWebsiteIsNotEmpty($websiteCode);
    }

    /**
     * @param mixed $websiteCode
     */
    private function validateWebsiteIsString($websiteCode)
    {
        if (!is_string($websiteCode)) {
            $type = $this->getVariableType($websiteCode);
            throw new InvalidWebsiteCodeException(sprintf('The website code must be a string, got "%s"', $type));
        }
    }

    /**
     * @param string $websiteCode
     */
    private function validateWebsiteIsNotEmpty($websiteCode)
    {
        if (empty(trim($websiteCode))) {
            throw new InvalidWebsiteCodeException('The website code can not be an empty string');
        }
    }

    /**
     * @return string
     */
    public function getDefaultCountry()
    {
        return $this->defaultCountry;
    }
}
