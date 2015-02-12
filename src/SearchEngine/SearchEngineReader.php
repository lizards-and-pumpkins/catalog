<?php

namespace Brera\SearchEngine;

class SearchEngineReader
{
    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @param SearchEngine $searchEngine
     */
    public function __construct(SearchEngine $searchEngine)
    {
        $this->searchEngine = $searchEngine;
    }

    /**
     * @param string $queryString
     * @return array
     */
    public function getSearchResults($queryString)
    {
        return $this->searchEngine->query($queryString);
    }
}
