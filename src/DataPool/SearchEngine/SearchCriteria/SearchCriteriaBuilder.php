<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformationRegistry;

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

    /**
     * @param string $fieldName
     * @param string $fieldValue
     * @return SearchCriteria
     */
    public function fromFieldNameAndValue($fieldName, $fieldValue)
    {
        if ($this->facetFieldTransformationRegistry->hasTransformationForCode($fieldName)) {
            $transformation = $this->facetFieldTransformationRegistry->getTransformationByCode($fieldName);
            $range = $transformation->decode($fieldValue);

            $criterionFrom = SearchCriterionGreaterOrEqualThan::create($fieldName, $range->from());
            $criterionTo = SearchCriterionLessOrEqualThan::create($fieldName, $range->to());

            return CompositeSearchCriterion::createAnd($criterionFrom, $criterionTo);
        }

        return SearchCriterionEqual::create($fieldName, $fieldValue);
    }

    /**
     * @param string[] $fieldNames
     * @param string $queryString
     * @return CompositeSearchCriterion
     */
    public function createCriteriaForAnyOfGivenFieldsContainsString(array $fieldNames, $queryString)
    {
        return CompositeSearchCriterion::createAnd(
            CompositeSearchCriterion::createOr(
                ...array_map(function ($fieldName) use ($queryString) {
                    return SearchCriterionLike::create($fieldName, $queryString);
                }, $fieldNames)
            ),
            $this->globalProductListingCriteria
        );
    }
}
