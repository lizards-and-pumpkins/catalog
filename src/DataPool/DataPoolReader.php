<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\InvalidKeyValueStoreKeyException;
use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Import\Product\ProductId;

class DataPoolReader
{
    /**
     * @var string
     */
    private $currentDataVersionKey = 'current_version';

    /**
     * @var string
     */
    private $currentDataVersionDefault = '-1';

    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @var UrlKeyStore
     */
    private $urlKeyStore;

    public function __construct(KeyValueStore $keyValueStore, SearchEngine $searchEngine, UrlKeyStore $urlKeyStore)
    {
        $this->keyValueStore = $keyValueStore;
        $this->searchEngine = $searchEngine;
        $this->urlKeyStore = $urlKeyStore;
    }

    public function hasSnippet(string $key) : bool
    {
        $this->validateKey($key);

        return $this->keyValueStore->has($key);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getSnippet(string $key)
    {
        $this->validateKey($key);

        return $this->keyValueStore->get($key);
    }

    /**
     * @param string $key
     * @return string[]
     */
    public function getChildSnippetKeys(string $key) : array
    {
        $this->validateKey($key);
        $json = $this->keyValueStore->get($key);
        $list = $this->decodeJsonArray($key, $json);

        return $list;
    }

    /**
     * @param string[] $keys
     * @return string[]
     */
    public function getSnippets(array $keys) : array
    {
        every($keys, function ($key) {
            $this->validateKey($key);
        });
        return $this->keyValueStore->multiGet(...$keys);
    }

    private function validateKey(string $key)
    {
        if ('' === $key) {
            throw new InvalidKeyValueStoreKeyException('The Key/Value storage key "" is invalid');
        }
    }

    /**
     * @param string $key
     * @param string $json
     * @return string[]
     */
    private function decodeJsonArray(string $key, string $json) : array
    {
        $result = json_decode($json, true);

        if ($result === false) {
            $result = [];
        }
        if (! is_array($result) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(sprintf('List for key "%s" is no valid JSON.', $key));
        }

        return $result;
    }

    public function getCurrentDataVersion() : string
    {
        if (! $this->keyValueStore->has($this->currentDataVersionKey)) {
            return $this->currentDataVersionDefault;
        }
        return $this->keyValueStore->get($this->currentDataVersionKey);
    }

    public function getSearchResultsMatchingCriteria(
        SearchCriteria $criteria,
        QueryOptions $queryOptions
    ) : SearchEngineResponse {
        return $this->searchEngine->query($criteria, $queryOptions);
    }

    /**
     * @param string $dataVersionString
     * @return string[]
     */
    public function getUrlKeysForVersion(string $dataVersionString) : array
    {
        return $this->urlKeyStore->getForDataVersion($dataVersionString);
    }

    /**
     * @param SearchCriteria $criteria
     * @param Context $context
     * @param SortOrderConfig $sortOrderConfig
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @return ProductId[]
     */
    public function getProductIdsMatchingCriteria(
        SearchCriteria $criteria,
        Context $context,
        SortOrderConfig $sortOrderConfig,
        int $rowsPerPage,
        int $pageNumber
    ) : array {
        $emptyFilterSelection = [];
        $includeNoFacetFiltersInResult = new FacetFiltersToIncludeInResult();

        $queryOptions = QueryOptions::create(
            $emptyFilterSelection,
            $context,
            $includeNoFacetFiltersInResult,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $searchResult = $this->searchEngine->query($criteria, $queryOptions);

        return array_values($searchResult->getProductIds());
    }

    public function getSearchResultsMatchingString(
        string $queryString,
        QueryOptions $queryOptions
    ) : SearchEngineResponse {
        return $this->searchEngine->queryFullText($queryString, $queryOptions);
    }
}
