<?php

declare(strict_types=1);

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
    public static function fromArray(array $fieldsArray) : SearchDocumentFieldCollection
    {
        $fields = [];

        foreach ($fieldsArray as $key => $value) {
            $valueArray = !is_array($value) ?
                [$value] :
                $value;
            $fields[$key] = SearchDocumentField::fromKeyAndValues($key, $valueArray);
        }

        return new self($fields);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->fields);
    }

    /**
     * @return \ArrayIterator|SearchDocumentField[]
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->fields);
    }

    /**
     * @return SearchDocumentField[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
