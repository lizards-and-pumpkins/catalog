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
    function __construct(PageKeyGenerator $keyGenerator, DataPoolReader $dataPoolReader)
    {
        $this->dataPoolReader = $dataPoolReader;
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * @param HttpUrl $url
     * @return Page
     */
    public function buildPage(HttpUrl $url)
    {
        $listKey = $this->keyGenerator->getKeyForSnippetList($url);

        $childKeys = $this->replacePlaceholdersInKeys($this->dataPoolReader->getChildSnippetKeys($listKey));
        $firstSnippetKey = $this->replacePlaceholdersInKeys($this->keyGenerator->getKeyForPage($url));

        $allSnippets = $this->dataPoolReader->getSnippets($childKeys + [$firstSnippetKey => $firstSnippetKey]);

        $content = $allSnippets[$firstSnippetKey];
        unset($allSnippets[$firstSnippetKey]);
        $childSnippets = $allSnippets;

        $snippets = $this->mergePlaceholderAndSnippets($this->buildPlaceholdersFromKeys($childKeys), $childSnippets);

        $content = $this->injectSnippetsIntoContent($content, $snippets);

        $page = new Page();
        $page->setBody($content);

        return $page;
    }

    /**
     * Take the snippet keys and fill in all placeholders
     *
     * @param array|string $snippetKeys
     * @return array|string
     */
    private function replacePlaceholdersInKeys($snippetKeys)
    {
        if (is_array($snippetKeys)) {

        } elseif (is_string($snippetKeys)) {

        }

        return $snippetKeys;
    }

    /**
     * @param array $snippetKeys
     * @return array
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
     * Changes keys and values, then replaces the snippet keys with the placeholders and flips it back
     *
     * @param array $snippetKeys
     * @param array $snippets
     * @return array
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
        do {
            $content = str_replace(array_keys($snippets), array_values($snippets), $content, $count);
        } while ($count);

        return $content;
    }
}
