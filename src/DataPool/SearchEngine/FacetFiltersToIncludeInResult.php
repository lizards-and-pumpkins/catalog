<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

class FacetFiltersToIncludeInResult
{
    /**
     * @var FacetFilterRequestField[]
     */
    private $fields;

    /**
     * @var string[]
     */
    private $memoizedAttributeCodeStrings;

    public function __construct(FacetFilterRequestField ...$fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return FacetFilterRequestField[]
     */
    public function getFields() : array
    {
        return $this->fields;
    }

    /**
     * @return string[]
     */
    public function getAttributeCodeStrings() : array
    {
        if (null === $this->memoizedAttributeCodeStrings) {
            $this->memoizedAttributeCodeStrings = array_map(function (FacetFilterRequestField $field) {
                return (string) $field->getAttributeCode();
            }, $this->fields);
        }

        return $this->memoizedAttributeCodeStrings;
    }
}
