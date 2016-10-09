<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\Exception\InvalidFacetFilterRangeBoundariesException;

class FacetFilterRange
{
    /**
     * @var int|float|string|null
     */
    private $rangeFrom;
    
    /**
     * @var int|float|string|null
     */
    private $rangeTo;

    /**
     * @param int|float|string|null $rangeFrom
     * @param int|float|string|null $rangeTo
     */
    private function __construct($rangeFrom, $rangeTo)
    {
        $this->rangeFrom = $rangeFrom;
        $this->rangeTo = $rangeTo;
    }

    /**
     * @param int|float|string|null $rangeFrom
     * @param int|float|string|null $rangeTo
     * @return FacetFilterRange
     */
    public static function create($rangeFrom, $rangeTo)
    {
        self::validateBoundaryType($rangeFrom);
        self::validateBoundaryType($rangeTo);

        if (null !== $rangeFrom && null !== $rangeTo &&
            (!is_numeric($rangeFrom) || !is_numeric($rangeTo)) &&
            gettype($rangeFrom) !== gettype($rangeTo)
        ) {
            throw new InvalidFacetFilterRangeBoundariesException('Facet filter rage boundaries must be the same type.');
        }

        return new self($rangeFrom, $rangeTo);
    }

    /**
     * @param int|float|string|null $boundary
     */
    private static function validateBoundaryType($boundary)
    {
        if (!is_int($boundary) && !is_string($boundary) && !is_float($boundary) && null !== $boundary) {
            throw new InvalidFacetFilterRangeBoundariesException(sprintf(
                'Facet filter range boundary must be numeric, string or null, got "%s".',
                gettype($boundary)
            ));
        }
    }

    /**
     * @return int|float|string|null
     */
    public function from()
    {
        return $this->rangeFrom;
    }

    /**
     * @return int|float|string|null
     */
    public function to()
    {
        return $this->rangeTo;
    }
}
