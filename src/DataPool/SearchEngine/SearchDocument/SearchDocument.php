<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

use Brera\Context\Context;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;

class SearchDocument
{
    /**
     * @var SearchDocumentFieldCollection
     */
    private $fields;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var string
     */
    private $content;

    /**
     * @param SearchDocumentFieldCollection $fields
     * @param Context $context
     * @param string $content
     */
    public function __construct(SearchDocumentFieldCollection $fields, Context $context, $content)
    {
        $this->fields = $fields;
        $this->context = $context;
        $this->content = (string) $content;
    }

    /**
     * @return SearchDocumentFieldCollection
     */
    public function getFieldsCollection()
    {
        return $this->fields;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param SearchCriteria $criteria
     * @return bool
     */
    public function isMatchingCriteria(SearchCriteria $criteria)
    {
        $isMatching = false;

        foreach ($criteria->getCriteria() as $criterion) {
            $isMatching = $this->hasMatchingField($criterion);

            if (($criteria->hasOrCondition() && $isMatching) || ($criteria->hasAndCondition() && !$isMatching)) {
                return $isMatching;
            }
        }

        return $isMatching;
    }

    /**
     * @param SearchCriterion $criterion
     * @return bool
     */
    private function hasMatchingField(SearchCriterion $criterion)
    {
        $isMatching = false;

        foreach ($this->fields->getFields() as $field) {
            if ($isMatching = $criterion->matches($field)) {
                return $isMatching;
            }
        }

        return $isMatching;
    }
}
