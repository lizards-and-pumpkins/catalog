<?php

namespace Brera;

use Brera\Http\HttpUrl;
use Brera\KeyValue\DataPoolReader;

class PageBuilder
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var PageKeyGenerator
     */
    private $keyGenerator;

    /**
     * @param PageKeyGenerator $keyGenerator
     * @param DataPoolReader $dataPoolReader
     */
    public function __construct(PageKeyGenerator $keyGenerator, DataPoolReader $dataPoolReader)
    {
        $this->dataPoolReader = $dataPoolReader;
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * @param HttpUrl $url
     * @return Page
     *
     * @todo deconstruct
     */
    public function buildPage(HttpUrl $url)
    {
        $snippetListKey = $this->keyGenerator->getKeyForSnippetList($url);

        $childSnippetKeys = $this->replacePlaceholdersInKeys(
            $this->dataPoolReader->getChildSnippetKeys($snippetListKey)
        );
        $rootSnippetKey = $this->replacePlaceholdersInKey($this->keyGenerator->getKeyForUrl($url));

        $allSnippets = $this->dataPoolReader->getSnippets($childSnippetKeys + [$rootSnippetKey => $rootSnippetKey]);

        $content = $allSnippets[$rootSnippetKey];
        unset($allSnippets[$rootSnippetKey]);
        $childSnippets = $allSnippets;

        $snippets = $this->mergePlaceholderAndSnippets(
            $this->buildPlaceholdersFromKeys($childSnippetKeys),
            $childSnippets
        );

        $content = $this->injectSnippetsIntoContent($content, $snippets);

        $page = new Page($content);

        return $page;
    }

    /**
     * @param string[] $snippetKeys
     * @return string[]
     */
    private function replacePlaceholdersInKeys(array $snippetKeys)
    {
        foreach ($snippetKeys as &$key) {
            $key = $this->replacePlaceholdersInKey($key);
        }

        return $snippetKeys;
    }

    /**
     * @todo Take the snippet keys and fill in all placeholders
     *
     * @param string $snippetKey
     * @return string
     */
    private function replacePlaceholdersInKey($snippetKey)
    {
        return $snippetKey;
    }

    /**
     * @todo have object which builts placeholders
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
        $content = $this->replaceAsLongAsSomethingIsReplaced($content, $snippets);

        return $content;
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
            echo '';
        } while ($count);

        return $content;
    }
}
