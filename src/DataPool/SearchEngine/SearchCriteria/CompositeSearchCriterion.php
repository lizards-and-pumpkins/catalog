<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\Exception\InvalidCriterionConditionException;

class CompositeSearchCriterion implements SearchCriteria
{
    const AND_CONDITION = 'and';
    const OR_CONDITION = 'or';

    /**
     * @var string
     */
    private $condition;

    /**
     * @var SearchCriteria[]
     */
    private $criteria = [];

    private function __construct(string $condition, SearchCriteria ...$criteria)
    {
        $this->condition = $condition;
        $this->criteria = $criteria;
    }

    public static function create(string $condition, SearchCriteria ...$criteria) : CompositeSearchCriterion
    {
        if (strcasecmp($condition, self::AND_CONDITION) === 0) {
            return new self($condition, ...$criteria);
        }

        if (strcasecmp($condition, self::OR_CONDITION) === 0) {
            return new self($condition, ...$criteria);
        }

        throw new InvalidCriterionConditionException(sprintf('Unknown search criteria condition "%s".', $condition));
    }

    public static function createAnd(SearchCriteria ...$criteria) : CompositeSearchCriterion
    {
        return new self(self::AND_CONDITION, ...$criteria);
    }

    public static function createOr(SearchCriteria ...$criteria) : CompositeSearchCriterion
    {
        return new self(self::OR_CONDITION, ...$criteria);
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize() : array
    {
        return [
            'condition' => $this->condition,
            'criteria'  => $this->criteria
        ];
    }
}
