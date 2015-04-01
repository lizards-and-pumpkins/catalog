<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;

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
     * @param string[] $fieldNamesAndValuesToCheck
     * @return bool
     */
    public function hasFieldMatchingOneOf(array $fieldNamesAndValuesToCheck)
    {
        foreach ($fieldNamesAndValuesToCheck as $fieldName => $fieldValue) {
            if ($this->hasMatchingField($fieldName, $fieldValue)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $fieldName
     * @param string $fieldValue
     * @return bool
     */
    public function hasMatchingField($fieldName, $fieldValue)
    {
        $searchField = SearchDocumentField::fromKeyAndValue($fieldName, $fieldValue);
        return $this->fields->contains($searchField);
    }

    /**
     * @param mixed[] $fieldNamesAndValuesToCheck
     * @throws InvalidFieldListSpecificationException
     */
    private function validateFieldListSpecification(array $fieldNamesAndValuesToCheck)
    {
        array_map(function ($fieldSpecification) {
            if (!is_array($fieldSpecification)) {
                throw new InvalidFieldListSpecificationException(sprintf(
                    'The field list has to specified as an array of arrays with a field name and value pair each'
                ));
            }
        }, $fieldNamesAndValuesToCheck);
    }
}
