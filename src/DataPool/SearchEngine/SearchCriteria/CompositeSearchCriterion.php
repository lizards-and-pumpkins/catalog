<?php

namespace Brera\DataPool\SearchEngine\SearchCriteria;

use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;

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

    /**
     * @param string $condition
     * @param SearchCriteria $criteria,...
     */
    private function __construct($condition, SearchCriteria ...$criteria)
    {
        $this->condition = $condition;
        $this->criteria = $criteria;
    }

    /**
     * @param SearchCriteria $criteria,...
     * @return CompositeSearchCriterion
     */
    public static function createAnd(SearchCriteria ...$criteria)
    {
        return new self(self::AND_CONDITION, ...$criteria);
    }

    /**
     * @param SearchCriteria $criteria,...
     * @return CompositeSearchCriterion
     */
    public static function createOr(SearchCriteria ...$criteria)
    {
        return new self(self::OR_CONDITION, ...$criteria);
    }

    /**
     * @param SearchDocument $searchDocument
     * @return bool
     */
    public function matches(SearchDocument $searchDocument)
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

    /**
     * @return bool
     */
    private function hasAndCondition()
    {
        return self::AND_CONDITION === $this->condition;
    }

    /**
     * @return bool
     */
    private function hasOrCondition()
    {
        return self::OR_CONDITION === $this->condition;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return [
            'condition' => $this->condition,
            'criteria'  => $this->criteria
        ];
    }
}
