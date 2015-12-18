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
     * @param string[] $fieldsArray
     * @return SearchDocumentFieldCollection
     */
    public static function fromArray(array $fieldsArray)
    {
        $fields = [];

        foreach ($fieldsArray as $key => $value) {
            $valueArray = !is_array($value) ?
                [$value] :
                $value;
            $fields[] = SearchDocumentField::fromKeyAndValues($key, $valueArray);
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
