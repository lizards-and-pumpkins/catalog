<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\Exception\InvalidTransformationCodeException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\Exception\UnableToFindTransformationException;

class FacetFieldTransformationRegistry
{
    /**
     * @var FacetFieldTransformation[]
     */
    private $transformations = [];

    public function register(string $code, FacetFieldTransformation $transformation): void
    {
        $this->validateCode($code);
        $this->transformations[$code] = $transformation;
    }

    public function getTransformationByCode(string $code) : FacetFieldTransformation
    {
        if (!$this->hasTransformationForCode($code)) {
            throw new UnableToFindTransformationException(
                sprintf('No facet field transformation with code "%s" is registered.', $code)
            );
        }

        return $this->transformations[$code];
    }

    public function hasTransformationForCode(string $code) : bool
    {
        self::validateCode($code);
        return isset($this->transformations[$code]);
    }

    private function validateCode(string $code): void
    {
        if (trim($code) === '') {
            throw new InvalidTransformationCodeException('Facet field transformation code must be a non-empty string.');
        }
    }
}
