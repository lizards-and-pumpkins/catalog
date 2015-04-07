<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

class SearchDocumentFieldCollection
{
    /**
     * @var SearchDocumentField[]
     */
    private $fields;

    /**
     * @param SearchDocumentField[] $searchDocumentFields
     */
    private function __construct(array $searchDocumentFields)
    {
        $this->fields = $searchDocumentFields;
    }

    /**
     * @param mixed[] $fieldsArray
     * @return SearchDocumentFieldCollection
     */
    public static function fromArray(array $fieldsArray)
    {
        $fields = [];

        foreach ($fieldsArray as $key => $val) {
            $fields[] = SearchDocumentField::fromKeyAndValue($key, $val);
        }

        return new self($fields);
    }

    /**
     * @return SearchDocumentField[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param SearchDocumentField $fieldToCheck
     * @return bool
     */
    public function contains(SearchDocumentField $fieldToCheck)
    {
        return in_array($fieldToCheck, $this->fields);
    }

    /**
     * @return string[]
     */
    public function toArray()
    {
        return array_reduce($this->fields, function(array $acc, SearchDocumentField $field) {
            $acc[$field->getKey()] = $field->getValue();
            return $acc;
        }, []);
    }
}
