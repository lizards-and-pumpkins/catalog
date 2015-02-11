<?php

namespace Brera;

use Brera\Environment\Environment;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpUrl;
use Brera\KeyValue\DataPoolReader;
use Brera\KeyValue\KeyNotFoundException;

class UrlKeyRequestHandler implements HttpRequestHandler
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var UrlPathKeyGenerator
     */
    private $urlPathKeyGenerator;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var HttpUrl
     */
    private $url;

    /**
     * @var string
     */
    private $rootSnippetKey;

    /**
     * @param HttpUrl $url
     * @param Environment $environment
     * @param UrlPathKeyGenerator $urlPathKeyGenerator
     * @param DataPoolReader $dataPoolReader
     */
    public function __construct(
        HttpUrl $url,
        Environment $environment,
        UrlPathKeyGenerator $urlPathKeyGenerator,
        DataPoolReader $dataPoolReader
    ) {
        $this->url = $url;
        $this->environment = $environment;
        $this->urlPathKeyGenerator = $urlPathKeyGenerator;
        $this->dataPoolReader = $dataPoolReader;
    }

    public function canProcess()
    {
        try {
            $this->getRootSnippetKey();
            return true;
        } catch (KeyNotFoundException $e) {
            return false;
        }
    }

    /**
     * @return Page
     * @todo deconstruct
     */
    public function process()
    {
        $rootSnippetKey = $this->getRootSnippetKey();
        $childSnippetKeys = $this->getChildSnippetKeys($rootSnippetKey);
        list($rootSnippet, $childSnippets) = $this->getSnippetsFromKeys($rootSnippetKey, $childSnippetKeys);

        $snippetKeysValueArray = $this->mergePlaceholderAndSnippets(
            $this->buildPlaceholdersFromKeys($childSnippetKeys),
            $childSnippets
        );

        $content = $this->injectSnippetsIntoContent($rootSnippet, $snippetKeysValueArray);

        return new Page($content);
    }

    /**
     * @param string[] $snippetKeys
     * @return string[]
     */
    private function replacePlaceholdersInKeys(array $snippetKeys)
    {
        // What is this for? (comment from vinai)
        foreach ($snippetKeys as &$key) {
            $key = $this->replacePlaceholdersInKey($key);
        }

        return $snippetKeys;
    }

    /**
     * @param string $key
     * @return string
     */
    private function replacePlaceholdersInKey($key)
    {
        // What is this for? (comment from vinai)
        return $key;
    }

    /**
     * @todo have object which builds placeholders
     *
     * @param string[] $snippetKeys
     * @return string[]
     */
    private function buildPlaceholdersFromKeys(array $snippetKeys)
    {
        $placeholders = [];

        foreach ($snippetKeys as $key) {
            $placeholders[$key] = "{{snippet $key}}";
        }

        return $placeholders;
    }

    /**
     * @param string[] $snippetKeys
     * @param string[] $snippets
     * @return string[]
     */
    private function mergePlaceholderAndSnippets(array $snippetKeys, array $snippets)
    {
        return array_combine($snippetKeys, $snippets);
    }

    /**
     * @param string $content
     * @param string[] $snippets
     * @return string
     */
    private function injectSnippetsIntoContent($content, array $snippets)
    {
        return $this->replaceAsLongAsSomethingIsReplaced($content, $snippets);
    }

    /**
     * @todo at the moment it doesn't make any difference in the tests whether the return
     * @todo is inside or outside of the loop - WHY!?!
     *
     * @param $content
     * @param string[] $snippets
     * @return string
     */
    private function replaceAsLongAsSomethingIsReplaced($content, array $snippets)
    {
        do {
            $content = str_replace(array_keys($snippets), array_values($snippets), $content, $count);
        } while ($count);

        return $content;
    }

    /**
     * @return string
     */
    private function getRootSnippetKey()
    {
        $this->memoizeRootSnippetKeyFromDataPool();
        return $this->rootSnippetKey;
    }

    /**
     * @return void
     */
    private function memoizeRootSnippetKeyFromDataPool()
    {
        if (is_null($this->rootSnippetKey)) {
            $this->rootSnippetKey = $this->dataPoolReader->getSnippet($this->getUrlSnippetKey());
        }
    }

    /**
     * @return string
     */
    private function getUrlSnippetKey()
    {
        return $this->urlPathKeyGenerator->getUrlKeyForUrlInEnvironment($this->url, $this->environment);
    }

    /**
     * @param string $rootSnippetKey
     * @return string[]
     */
    private function getChildSnippetKeys($rootSnippetKey)
    {
        $childSnippetListKey = $this->urlPathKeyGenerator->getChildSnippetListKey($rootSnippetKey);
        $childSnippetKeys = $this->replacePlaceholdersInKeys(
            $this->dataPoolReader->getChildSnippetKeys($childSnippetListKey)
        );
        return $childSnippetKeys;
    }

    /**
     * @param string $rootSnippetKey
     * @param array $childSnippetKeys
     * @return array
     */
    private function getSnippetsFromKeys($rootSnippetKey, array $childSnippetKeys)
    {
        $rootSnippetArray = [$rootSnippetKey => $rootSnippetKey];
        $allSnippets = $this->dataPoolReader->getSnippets($childSnippetKeys + $rootSnippetArray);

        $content = $allSnippets[$rootSnippetKey];
        $childSnippets = array_diff_key($allSnippets, $rootSnippetArray);
        return array($content, $childSnippets);
    }
}
