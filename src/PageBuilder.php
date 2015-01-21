<?php


namespace Brera;


use Brera\Http\HttpUrl;
use Brera\KeyValue\DataPoolReader;

class PageBuilder
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var Environment
     */
    private $environment;
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @param HttpUrl $url
     * @param Environment $environment
     * @param DataPoolReader $dataPoolReader
     */
    function __construct(
        HttpUrl $url,
        Environment $environment,
        DataPoolReader $dataPoolReader
    ) {
        $this->url = $url;
        $this->environment = $environment;
        $this->dataPoolReader = $dataPoolReader;
    }


    /**
     * return Page
     */
    public function buildPage()
    {
        // todo how to get the keygenerator in here?
        // todo I don't think injecting is a good idea, because it is the responsibility of the builder to get it
        // todo if we inject it, we can omit the url and env
        $keyGenerator = new PageKeyGenerator();
        $firstSnippetKey = $keyGenerator->getKeyForSnippet(
            $this->url, $this->environment
        );

        $listKey = $keyGenerator->getKeyForSnippetList(
            $this->url, $this->environment
        );

        $content = $this->dataPoolReader->getSnippet($firstSnippetKey);

        $snippetKeys = $this->dataPoolReader->getSnippetList($listKey);
        // TODO replace all placeholders in placeholders!
        $snippetKeys = $this->finishSnippetKeys($snippetKeys);

        $snippets = $this->dataPoolReader->getSnippets($snippetKeys);

        $snippetKeys = $this->builtPlaceholderFromKeys($snippetKeys);

        $snippets = $this->mergePlaceholderAndSnippets($snippetKeys, $snippets);

        $content = $this->mergeSnippets($content, $snippets);

        $page = new Page();
        $page->setBody($content);

        return $page;
    }

    /**
     * Take the snippet keys and fill in all placeholders
     *
     * @param array $snippetKeys
     * @todo
     * @return array
     */
    private function finishSnippetKeys(array $snippetKeys)
    {
        return $snippetKeys;
    }

    /**
     * @param array $snippetKeys
     * @return array
     */
    private function builtPlaceholderFromKeys(array $snippetKeys)
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
        // TODO theoretically this is unneeded, but only, if we can be sure, that the snippets are in right order from data pool reader!
        ksort($snippetKeys);
        ksort($snippets);

        return array_combine($snippetKeys, $snippets);
    }

    private function mergeSnippets($content, $snippets)
    {
        do {
            // replace, as long something is replaced
            $content = str_replace(array_keys($snippets), array_values($snippets), $content, $count);
        } while ($count);

        return $content;
    }
}
