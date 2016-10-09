<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\Exception\InvalidCriterionConditionException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;

class CompositeSearchCriterion implements SearchCriteria, \JsonSerializable
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

    public function matches(SearchDocument $searchDocument) : bool
    {
        $isMatching = false;

        foreach ($this->criteria as $criteria) {
            $isMatching = $criteria->matches($searchDocument);
            if (($this->hasOrCondition() && $isMatching) || ($this->hasAndCondition() && !$isMatching)) {
                return $isMatching;
            }
        }

        return $isMatching;
    }

    private function hasAndCondition() : bool
    {
        return self::AND_CONDITION === $this->condition;
    }

    private function hasOrCondition() : bool
    {
        return self::OR_CONDITION === $this->condition;
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
