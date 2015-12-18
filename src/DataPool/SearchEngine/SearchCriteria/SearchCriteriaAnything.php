<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;

class SearchCriteriaAnything implements SearchCriteria, \JsonSerializable
{
    private function __construct()
    {
    }

    /**
     * @return SearchCriteriaAnything
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @param SearchDocument $searchDocument
     * @return bool
     */
    public function matches(SearchDocument $searchDocument)
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function jsonSerialize()
    {
        return [
            'fieldName'  => '',
            'fieldValue' => '',
            'operation'  => 'Anything'
        ];
    }
}