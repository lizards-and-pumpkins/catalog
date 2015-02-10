<?php

namespace Brera\SearchEngine;

interface SearchEngine
{
    /**
     * @param array $entry
     * @return void
     */
    public function addToIndex(array $entry);

    /**
     * @param array $entries
     * @return void
     */
    public function addMultiToIndex(array $entries);

    /**
     * @param string $queryString
     * @return array
     */
    public function query($queryString);
}
