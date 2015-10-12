<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\Exception\InvalidFacetFieldValueCountException;
use LizardsAndPumpkins\DataPool\SearchEngine\Exception\InvalidFacetFieldValueException;

class SearchEngineFacetFieldValueCount
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var int
     */
    private $count;

    /**
     * @param string $value
     * @param int $count
     */
    private function __construct($value, $count)
    {
        $this->value = $value;
        $this->count = $count;
    }

    /**
     * @param mixed $value
     * @param mixed $count
     * @return SearchEngineFacetFieldValueCount
     */
    public static function create($value, $count)
    {
        if (!is_string($value)) {
            throw new InvalidFacetFieldValueException(
                sprintf('Facet field value must be string, "%s" given', gettype($value))
            );
        }

        if (!is_int($count)) {
            throw new InvalidFacetFieldValueCountException(
                sprintf('Facet field value count must be integer, "%s" given', gettype($count))
            );
        }

        return new self($value, $count);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }
}
