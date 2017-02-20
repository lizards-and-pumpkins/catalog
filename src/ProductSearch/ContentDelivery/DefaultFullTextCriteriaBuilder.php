<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionFullText;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\EmptyQueryStringException;

class DefaultFullTextCriteriaBuilder implements FullTextCriteriaBuilder
{
    /**
     * @var string
     */
    private $fullTextSearchTermCombinationOperator;

    public function __construct(string $fullTextSearchTermCombinationOperator)
    {
        $this->fullTextSearchTermCombinationOperator = $fullTextSearchTermCombinationOperator;
    }

    public function createFromString(string $queryString): SearchCriteria
    {
        if (trim($queryString) === '') {
            throw new EmptyQueryStringException('Query string must not be empty.');
        }

        if (strpos($queryString, ' ') === false) {
            return new SearchCriterionFullText($queryString);
        }

        $values = array_filter(explode(' ', $queryString));
        $criteria = array_map(function (string $value) {
            return new SearchCriterionFullText($value);
        }, $values);

        return CompositeSearchCriterion::create($this->fullTextSearchTermCombinationOperator, ...$criteria);
    }
}
