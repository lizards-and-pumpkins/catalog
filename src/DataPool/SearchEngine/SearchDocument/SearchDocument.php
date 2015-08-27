<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

use Brera\Context\Context;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;
use Brera\Product\ProductId;

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
     * @var ProductId
     */
    private $productId;

    public function __construct(SearchDocumentFieldCollection $fields, Context $context, ProductId $productId)
    {
        $this->fields = $fields;
        $this->context = $context;
        $this->productId = $productId;
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
     * @return ProductId
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param SearchCriteria $criteria
     * @return bool
     */
    public function isMatchingCriteria(SearchCriteria $criteria)
    {
        $isMatching = false;

        foreach ($criteria->getCriteria() as $criterion) {
            if ($criterion instanceof SearchCriteria) {
                $isMatching = $this->isMatchingCriteria($criterion);
            }

            if ($criterion instanceof SearchCriterion) {
                $isMatching = $this->hasMatchingField($criterion);
            }

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
        foreach ($this->fields->getFields() as $field) {
            if ($criterion->matches($field)) {
                return true;
            }
        }

        return false;
    }
}
