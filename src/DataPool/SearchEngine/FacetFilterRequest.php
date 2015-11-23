<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

class FacetFilterRequest
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
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return string[]
     */
    public function getAttributeCodeStrings()
    {
        if (null === $this->memoizedAttributeCodeStrings) {
            $this->memoizedAttributeCodeStrings = array_map(function (FacetFilterRequestField $field) {
                return (string) $field->getAttributeCode();
            }, $this->fields);
        }

        return $this->memoizedAttributeCodeStrings;
    }
}
