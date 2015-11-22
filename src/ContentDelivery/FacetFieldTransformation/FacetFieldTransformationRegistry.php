<?php

namespace LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation;

use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\Exception\InvalidTransformationCodeException;
use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\Exception\UnableToFindTransformationException;

class FacetFieldTransformationRegistry
{
    /**
     * @var FacetFieldTransformation[]
     */
    private $transformations = [];

    public function register($code, FacetFieldTransformation $transformation)
    {
        $this->validateCode($code);
        $this->transformations[$code] = $transformation;
    }

    /**
     * @param string $code
     * @return FacetFieldTransformation
     */
    public function getTransformationByCode($code)
    {
        self::validateCode($code);

        if (!isset($this->transformations[$code])) {
            throw new UnableToFindTransformationException(
                sprintf('No facet field transformation with code "%s" is registered.', $code)
            );
        }

        return $this->transformations[$code];
    }

    /**
     * @param string $code
     */
    private function validateCode($code)
    {
        if (!is_string($code) || trim($code) === '') {
            throw new InvalidTransformationCodeException('Facet field transformation code must be a non-empty string.');
        }
    }
}
