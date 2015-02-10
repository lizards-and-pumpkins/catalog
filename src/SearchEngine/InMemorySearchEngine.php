<?php

namespace Brera\SearchEngine;

class InMemorySearchEngine implements SearchEngine
{
    /**
     * @var array
     */
    private $index = [];

    /**
     * @param array $entry
     * @return void
     */
    public function addToIndex(array $entry)
    {
        array_push($this->index, $entry);
    }

    /**
     * @param array $entries
     * @return void
     */
    public function addMultiToIndex(array $entries)
    {
        foreach ($entries as $entry) {
            $this->addToIndex($entry);
        }
    }

    /**
     * @param string $queryString
     * @return array
     */
    public function query($queryString)
    {
        $results = [];

        foreach ($this->index as $entry) {
            foreach ($entry as $field) {
                if (false !== stripos($field, $queryString)) {
                    array_push($results, $entry);
                }
            }
        }

        return $results;
    }
}
