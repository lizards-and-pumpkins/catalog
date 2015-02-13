<?php

namespace Brera\KeyValue;

use Brera\Environment\Environment;

interface SearchEngine
{
    /**
     * @param searchDocument $searchDocument
     * @return void
     */
    public function addSearchDocument(searchDocument $searchDocument);

    /**
     * @param SearchDocumentCollection $searchDocumentCollection
     * @return void
     */
    public function addSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection);

    /**
     * @param string $queryString
     * @param Environment $environment
     * @return array
     */
    public function query($queryString, Environment $environment);
}
