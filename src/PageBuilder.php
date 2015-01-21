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
        $snippetKey = $keyGenerator->getKeyForSnippet(
            $this->url, $this->environment
        );

        $listKey = $keyGenerator->getKeyForSnippetList(
            $this->url, $this->environment
        );

        $content = $this->dataPoolReader->getSnippet($snippetKey);

        $page = new Page();
        $page->setBody($content);

        return $page;
    }
}
