<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\Exception\InvalidTransformationCodeException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\Exception\UnableToFindTransformationException;

class FacetFieldTransformationRegistry
{
    /**
     * @var FacetFieldTransformation[]
     */
    private $transformations = [];

    /**
     * @param string $code
     * @param FacetFieldTransformation $transformation
     */
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
        if (!$this->hasTransformationForCode($code)) {
            throw new UnableToFindTransformationException(
                sprintf('No facet field transformation with code "%s" is registered.', $code)
            );
        }

        return $this->transformations[$code];
    }

    /**
     * @param string $code
     * @return bool
     */
    public function hasTransformationForCode($code)
    {
        self::validateCode($code);
        return isset($this->transformations[$code]);
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
