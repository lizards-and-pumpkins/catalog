<?php


namespace Brera;


use Brera\KeyValue\KeyValueStore;

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
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @param string        $url
     * @param Environment   $environment
     * @param KeyValueStore $keyValueStore
     */
    function __construct(
        $url,
        Environment $environment,
        KeyValueStore $keyValueStore
    ) {
        $this->url = $url;
        $this->environment = $environment;
        $this->keyValueStore = $keyValueStore;
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
        $snippetKey = $keyGenerator->getKeyForSnippet(
            $this->url, $this->environment
        );

        $listKey = $keyGenerator->getKeyForSnippetList(
            $this->url, $this->environment
        );

        // todo which format is the list?
        $list = $this->keyValueStore->get($listKey);



        $page = new Page();
        $content = $this->keyValueStore->get($snippetKey);
        $page->setBody($content);
        return $page;
    }
}
