<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

class SearchDocumentFieldCollection implements \Countable, \IteratorAggregate
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
     * @return int
     */
    public function count()
    {
        return count($this->fields);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->fields);
    }

    /**
     * @return SearchDocumentField[]
     */
    public function getFields()
    {
        return $this->fields;
    }
}
