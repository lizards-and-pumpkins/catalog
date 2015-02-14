<?php

namespace Brera\DataPool\SearchEngine;

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
            $fields[] = new SearchDocumentField($key, $val);
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
}
