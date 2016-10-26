<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;

class SearchCriterionAnything implements SearchCriteria, \JsonSerializable
{
    public function matches(SearchDocument $searchDocument) : bool
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function jsonSerialize() : array
    {
        return [
            'fieldName'  => '',
            'fieldValue' => '',
            'operation'  => 'Anything'
        ];
    }
}
