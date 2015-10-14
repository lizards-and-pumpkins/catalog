<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Utils\Clearable;

abstract class IntegrationTestSearchEngineAbstract implements SearchEngine, Clearable
{
    /**
     * @return SearchDocument[]
     */
    abstract protected function getSearchDocuments();

    /**
     * @inheritdoc
     */
    final public function query($queryString, Context $queryContext, array $facetFields, $rowsPerPage, $pageNumber)
    {
        $allDocuments = $this->getSearchDocuments();
        $matchingDocuments = $this->getSearchDocumentsForQueryInContext($allDocuments, $queryString, $queryContext);

        return $this->createSearchEngineResponse($facetFields, $matchingDocuments, $rowsPerPage, $pageNumber);
    }

    /**
     * @param SearchDocument[] $searchDocuments
     * @param string $queryString
     * @param Context $queryContext
     * @return SearchDocument[]
     */
    private function getSearchDocumentsForQueryInContext(array $searchDocuments, $queryString, Context $queryContext)
    {
        $docsMatchingContext = $this->getSearchDocumentsMatchingContext($searchDocuments, $queryContext);
        return $this->getSearchDocumentsMatchingQueryString($docsMatchingContext, $queryString);
    }

    /**
     * @param SearchDocument[] $searchDocuments
     * @param Context $context
     * @return SearchDocument[]
     */
    private function getSearchDocumentsMatchingContext(array $searchDocuments, Context $context)
    {
        return array_filter($searchDocuments, function (SearchDocument $searchDocument) use ($context) {
            return $this->hasMatchingContext($context, $searchDocument);
        });
    }

    /**
     * @param SearchDocument[] $searchDocuments
     * @param string $queryString
     * @return SearchDocument[]
     */
    private function getSearchDocumentsMatchingQueryString(array $searchDocuments, $queryString)
    {
        return array_filter($searchDocuments, function (SearchDocument $searchDocument) use ($queryString) {
            return $this->isAnyFieldValueOfSearchDocumentMatchesQueryString($searchDocument, $queryString);
        });
    }

    /**
     * @inheritdoc
     */
    final public function getSearchDocumentsMatchingCriteria(
        SearchCriteria $criteria,
        Context $context,
        array $facetFields,
        $rowsPerPage,
        $pageNumber
    ) {
        $matchingDocuments = array_filter(
            $this->getSearchDocuments(),
            function (SearchDocument $searchDocument) use ($criteria, $context) {
                return $criteria->matches($searchDocument) && $context->isSubsetOf($searchDocument->getContext());
            }
        );

        return $this->createSearchEngineResponse($facetFields, $matchingDocuments, $rowsPerPage, $pageNumber);
    }

    /**
     * @param SearchDocument $searchDocument
     * @return string
     */
    final protected function getSearchDocumentIdentifier(SearchDocument $searchDocument)
    {
        return $searchDocument->getProductId() . ':' . $searchDocument->getContext()->toString();
    }

    /**
     * @param Context $queryContext
     * @param SearchDocument $searchDocument
     * @return bool
     */
    private function hasMatchingContext(Context $queryContext, SearchDocument $searchDocument)
    {
        $hasAtLeastOneMatchingContextPart = false;
        $documentContext = $searchDocument->getContext();
        foreach ($queryContext->getSupportedCodes() as $code) {
            if ($documentContext->supportsCode($code)) {
                if (!$this->hasMatchingContextValue($queryContext, $documentContext, $code)) {
                    return false;
                }
                $hasAtLeastOneMatchingContextPart = true;
            }
        }
        return $hasAtLeastOneMatchingContextPart;
    }

    /**
     * @param Context $queryContext
     * @param Context $documentContext
     * @param string $code
     * @return bool
     */
    private function hasMatchingContextValue(Context $queryContext, Context $documentContext, $code)
    {
        return $queryContext->getValue($code) === $documentContext->getValue($code);
    }

    /**
     * @param SearchDocument $searchDocument
     * @param string $queryString
     * @return bool
     */
    private function isAnyFieldValueOfSearchDocumentMatchesQueryString(SearchDocument $searchDocument, $queryString)
    {
        /** @var SearchDocumentField $field */
        foreach ($searchDocument->getFieldsCollection() as $field) {
            if ($this->isFieldWithMatchingValue($field, $queryString)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param SearchDocumentField $field
     * @param string $queryString
     * @return bool
     */
    private function isFieldWithMatchingValue(SearchDocumentField $field, $queryString)
    {
        foreach ($field->getValues() as $value) {
            if (stripos($value, $queryString) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string[] $facetFieldCodes
     * @param SearchDocument ...$searchDocuments
     * @return SearchEngineFacetFieldCollection
     */
    private function createFacetFieldsCollectionFromSearchDocuments(
        array $facetFieldCodes,
        SearchDocument ...$searchDocuments
    ) {
        $attributeCounts = $this->createAttributeValueCountArrayFromSearchDocuments(
            $facetFieldCodes,
            ...$searchDocuments
        );
        $facetFields = array_map(function ($attributeCode, $attributeValues) {
            $facetFieldValues = $this->getFacetFieldValuesFromAttributeValues($attributeValues);
            return new SearchEngineFacetField(AttributeCode::fromString($attributeCode), ...$facetFieldValues);
        }, array_keys($attributeCounts), $attributeCounts);

        return new SearchEngineFacetFieldCollection(...$facetFields);
    }

    /**
     * @param string[] $facetFieldCodes
     * @param SearchDocument ...$searchDocuments
     * @return array[]
     */
    private function createAttributeValueCountArrayFromSearchDocuments(
        array $facetFieldCodes,
        SearchDocument ...$searchDocuments
    ) {
        return array_reduce($searchDocuments, function ($carry, SearchDocument $document) use ($facetFieldCodes) {
            return array_reduce(
                $document->getFieldsCollection()->getFields(),
                function ($carry, SearchDocumentField $documentField) use ($facetFieldCodes) {
                    return in_array($documentField->getKey(), $facetFieldCodes) ?
                        $this->addDocumentFieldValuesToCount($carry, $documentField->getKey(), $documentField) :
                        $carry;
                },
                $carry
            );
        }, []);
    }

    /**
     * @param array[] $attributeValuesCounts
     * @param string $attributeCode
     * @param SearchDocumentField $documentField
     * @return array[]
     */
    private function addDocumentFieldValuesToCount(
        array $attributeValuesCounts,
        $attributeCode,
        SearchDocumentField $documentField
    ) {
        return array_reduce($documentField->getValues(), function ($carry, $value) use ($attributeCode) {
            return $this->addValueToCount($carry, $attributeCode, $value);
        }, $attributeValuesCounts);
    }

    /**
     * @param array[] $attributeValuesCounts
     * @param string $attributeCode
     * @param string $newValue
     * @return array[]
     */
    private function addValueToCount(array $attributeValuesCounts, $attributeCode, $newValue)
    {
        $valueWasFound = false;
        $newValues = [];

        foreach ($attributeValuesCounts as $currentAttributeCode => $currentAttributeValues) {
            if ($currentAttributeCode !== $attributeCode) {
                $newValues[$currentAttributeCode] = $currentAttributeValues;
                continue;
            }

            $newAttributeValues = [];
            foreach ($currentAttributeValues as $currentValueArray) {
                if ($currentValueArray['value'] === $newValue) {
                    $currentValueArray['count']++;
                    $valueWasFound = true;
                }
                $newAttributeValues[] = $currentValueArray;
            }

            $newValues[$currentAttributeCode] = $newAttributeValues;
        }

        if (false === $valueWasFound) {
            $newValues[$attributeCode][] = ['value' => $newValue, 'count' => 1];
        }

        return $newValues;
    }

    /**
     * @param string[] $facetFields
     * @param SearchDocument[] $searchDocuments
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @return SearchEngineResponse
     */
    private function createSearchEngineResponse(array $facetFields, array $searchDocuments, $rowsPerPage, $pageNumber)
    {
        $facetFieldCollection = $this->createFacetFieldsCollectionFromSearchDocuments(
            $facetFields,
            ...array_values($searchDocuments)
        );

        $totalNumberOfResults = count($searchDocuments);
        $currentPageDocuments = array_slice($searchDocuments, $pageNumber * $rowsPerPage, $rowsPerPage);
        $documentCollection = new SearchDocumentCollection(...array_values($currentPageDocuments));

        return new SearchEngineResponse($documentCollection, $facetFieldCollection, $totalNumberOfResults);
    }

    /**
     * @param int[] $attributeValues
     * @return SearchEngineFacetFieldValueCount[]
     */
    private function getFacetFieldValuesFromAttributeValues($attributeValues)
    {
        return array_map(function ($valueCounts) {
            return SearchEngineFacetFieldValueCount::create((string) $valueCounts['value'], $valueCounts['count']);
        }, $attributeValues);
    }
}
