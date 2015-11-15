<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\InvalidRangeFormatException;

class FacetFieldRangeCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var FacetFieldRange[]
     */
    private $facetFieldRanges;

    /**
     * @var string
     */
    private $rangeOutputFormat;

    /**
     * @var string
     */
    private $rangeInputFormat;

    /**
     * @param string $rangeOutputFormat
     * @param string $rangeInputFormat
     * @param FacetFieldRange[] ...$facetFieldRanges
     */
    private function __construct($rangeOutputFormat, $rangeInputFormat, FacetFieldRange ...$facetFieldRanges)
    {
        $this->facetFieldRanges = $facetFieldRanges;
        $this->rangeOutputFormat = $rangeOutputFormat;
        $this->rangeInputFormat = $rangeInputFormat;
    }

    /**
     * @param string $rangeOutputFormat
     * @param string $rangeInputFormat
     * @param FacetFieldRange[] ...$facetFieldRanges
     * @return FacetFieldRangeCollection
     */
    public static function create($rangeOutputFormat, $rangeInputFormat, FacetFieldRange ...$facetFieldRanges)
    {
        self::validateRangeFormat($rangeOutputFormat);
        self::validateRangeFormat($rangeInputFormat);

        return new self($rangeOutputFormat, $rangeInputFormat, ...$facetFieldRanges);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->facetFieldRanges);
    }

    /**
     * @return FacetFieldRange[]
     */
    public function getRanges()
    {
        return $this->facetFieldRanges;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->facetFieldRanges);
    }

    /**
     * @return string
     */
    public function getRangeOutputFormat()
    {
        return $this->rangeOutputFormat;
    }

    /**
     * @return string
     */
    public function getRangeInputFormat()
    {
        return $this->rangeInputFormat;
    }

    /**
     * @param mixed $rangeFormat
     */
    private static function validateRangeFormat($rangeFormat)
    {
        if (!is_string($rangeFormat)) {
            throw new InvalidRangeFormatException(
                sprintf('Range format must be string, got "%s".', gettype($rangeFormat))
            );
        }

        $numberOfPlaceholdersInRangeOutputFormat = substr_count($rangeFormat, '%s');
        if (2 !== $numberOfPlaceholdersInRangeOutputFormat) {
            throw new InvalidRangeFormatException(sprintf(
                'Range output must contain 2 "%%s" placeholders, found "%d".',
                $numberOfPlaceholdersInRangeOutputFormat
            ));
        }
    }
}
