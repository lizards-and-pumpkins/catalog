<?php

namespace Brera;

use Brera\Context\VersionedContext;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore;
use Brera\DataPool\SearchEngine\InMemorySearchEngine;
use Brera\DataPool\KeyValue\KeyValueStore;

class FrontendRenderingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @return void
     */
    public function itShouldRenderAPageFromAnUrlWithoutVariablesInSnippets()
    {
        $url = HttpUrl::fromString('http://example.com/product1');
        $context = new VersionedContext(DataVersion::fromVersionString('1.0'));

        $keyValueStore = new InMemoryKeyValueStore();
        $searchEngine = new InMemorySearchEngine();

        $this->addBaseSnippetAndListToKeyValueStorage($keyValueStore);
        $this->addSnippetsForReplacementToTheKeyValueStorage($keyValueStore);

        $dataPoolReader = new DataPoolReader($keyValueStore, $searchEngine);

        $urlPathKeyGenerator = new PoCUrlPathKeyGenerator();

        $pageBuilder = new UrlKeyRequestHandler(
            $url,
            $context,
            $urlPathKeyGenerator,
            $dataPoolReader
        );
        $page = $pageBuilder->process();

        $body = $page->getBody();
        $expected = '<html><head><title>Page Title</title></head><body><h1>Headline</h1></body></html>';

        $this->assertEquals($expected, $body);
    }

    /**
     * @param KeyValueStore $keyValueStore
     * @return void
     */
    private function addBaseSnippetAndListToKeyValueStorage(KeyValueStore $keyValueStore)
    {
        $keyValueStore->set(
            '_product1_v:1_0',
            'test_root_key'
        );
        $keyValueStore->set(
            'test_root_key',
            '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>'
        );
        $keyValueStore->set(
            'test_root_key_l',
            json_encode(['head', 'body'])
        );
    }

    /**
     * @param KeyValueStore $keyValueStore
     * @return void
     */
    private function addSnippetsForReplacementToTheKeyValueStorage(KeyValueStore $keyValueStore)
    {
        $keyValueStore->set('head', '<title>Page Title</title>');
        $keyValueStore->set('body', '<h1>Headline</h1>');
    }
}
