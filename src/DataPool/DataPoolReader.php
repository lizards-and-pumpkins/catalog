<?php

namespace Brera\DataPool;

use Brera\DataPool\KeyValue\KeyValueStore;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\DataPool\SearchEngine\SearchEngine;
use Brera\Context\Context;

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

    public function __construct(KeyValueStore $keyValueStore, SearchEngine $searchEngine)
    {
        $this->keyValueStore = $keyValueStore;
        $this->searchEngine = $searchEngine;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasSnippet($key)
    {
        $this->validateKey($key);
        
        return $this->keyValueStore->has($key);
    }

    /**
     * @param string $key
     * @return string
     */
    public function getSnippet($key)
    {
        $this->validateKey($key);

        return $this->keyValueStore->get($key);
    }

    /**
     * @param string $key
     * @return string[]
     */
    public function getChildSnippetKeys($key)
    {
        $this->validateKey($key);
        $json = $this->keyValueStore->get($key);
        $this->validateJson($key, $json);
        $list = $this->decodeJsonArray($key, $json);

        return $list;
    }

    /**
     * @param string[] $keys
     * @return string[]
     */
    public function getSnippets($keys)
    {
        if (!is_array($keys)) {
            throw new \RuntimeException(
                sprintf('multiGet needs an array to operated on, your keys is of type %s.', gettype($keys))
            );
        }
        foreach ($keys as $key) {
            $this->validateKey($key);
        }

        return $this->keyValueStore->multiGet($keys);
    }

    /**
     * @param string $key
     */
    private function validateKey($key)
    {
        if (!is_string($key)) {
            throw new InvalidKeyValueStoreKeyException('The key is not of type string.');
        }
        if ('' === $key) {
            throw new InvalidKeyValueStoreKeyException('The Key/Value storage key "" is invalid');
        }
    }

    /**
     * @param string $key
     * @param string $json
     */
    private function validateJson($key, $json)
    {
        if (!is_string($json)) {
            throw new \RuntimeException(
                sprintf(
                    'Expected the value for key "%s" to be a string containing JSON but found "%s".',
                    $key,
                    gettype($json)
                )
            );
        }
    }

    /**
     * @param string $key
     * @param string $json
     * @return string[]
     */
    private function decodeJsonArray($key, $json)
    {
        $result = json_decode($json, true);

        if ($result === false) {
            $result = [];
        }
        if (!is_array($result) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(sprintf('List for key "%s" is no valid JSON.', $key));
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getCurrentDataVersion()
    {
        if (! $this->keyValueStore->has($this->currentDataVersionKey)) {
            return $this->currentDataVersionDefault;
        }
        return $this->keyValueStore->get($this->currentDataVersionKey);
    }

    /**
     * @param string $queryString
     * @param Context $context
     * @return SearchDocumentCollection
     */
    public function getSearchResults($queryString, Context $context)
    {
        return $this->searchEngine->query($queryString, $context);
    }

    /**
     * @param SearchCriteria $criteria
     * @param Context $context
     * @return SearchDocumentCollection
     */
    public function getSearchDocumentsMatchingCriteria(SearchCriteria $criteria, Context $context)
    {
        return $this->searchEngine->getSearchDocumentsMatchingCriteria($criteria, $context);
    }
}
