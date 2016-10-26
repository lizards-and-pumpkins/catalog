<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;

class SearchCriteriaBuilder
{
    /**
     * @var FacetFieldTransformationRegistry
     */
    private $facetFieldTransformationRegistry;

    /**
     * @var SearchCriteria
     */
    private $globalProductListingCriteria;

    public function __construct(
        FacetFieldTransformationRegistry $facetFieldTransformationRegistry,
        SearchCriteria $globalProductListingCriteria
    ) {
        $this->facetFieldTransformationRegistry = $facetFieldTransformationRegistry;
        $this->globalProductListingCriteria = $globalProductListingCriteria;
    }

    public function fromFieldNameAndValue(string $fieldName, string $fieldValue) : SearchCriteria
    {
        if ($this->facetFieldTransformationRegistry->hasTransformationForCode($fieldName)) {
            $transformation = $this->facetFieldTransformationRegistry->getTransformationByCode($fieldName);
            $range = $transformation->decode($fieldValue);

            $criterionFrom = new SearchCriterionGreaterOrEqualThan($fieldName, $range->from());
            $criterionTo = new SearchCriterionLessOrEqualThan($fieldName, $range->to());

            return CompositeSearchCriterion::createAnd($criterionFrom, $criterionTo);
        }

        return new SearchCriterionEqual($fieldName, $fieldValue);
    }

    /**
     * @param string[] $fieldNames
     * @param string $queryString
     * @return CompositeSearchCriterion
     */
    public function createCriteriaForAnyOfGivenFieldsContainsString(
        array $fieldNames,
        string $queryString
    ) : CompositeSearchCriterion {
        return CompositeSearchCriterion::createAnd(
            CompositeSearchCriterion::createOr(
                ...array_map(function ($fieldName) use ($queryString) {
                    return new SearchCriterionLike($fieldName, $queryString);
                }, $fieldNames)
            ),
            $this->globalProductListingCriteria
        );
    }
}
