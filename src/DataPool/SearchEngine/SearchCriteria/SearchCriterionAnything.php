<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

class SearchCriterionAnything implements SearchCriteria
{
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
