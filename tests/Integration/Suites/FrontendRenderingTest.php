<?php

namespace Brera;

use Brera\Http\HttpUrl;
use Brera\KeyValue\DataPoolReader;
use Brera\KeyValue\InMemory\InMemoryKeyValueStore;
use Brera\KeyValue\KeyValueStore;
use Brera\KeyValue\KeyValueStoreKeyGenerator;

class FrontendRenderingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldRenderAPageFromAUrlWithoutVariablesInSnippets()
    {
        $url = HttpUrl::fromString('http://example.com/product1');
        $environment = new VersionedEnvironment(DataVersion::fromVersionString('1.0'));

        $keyValueStore = new InMemoryKeyValueStore();

        $this->addBaseSnippetAndListToKeyValueStorage($keyValueStore);
        $this->addSnippetsForReplacementToTheKeyValueStorage($keyValueStore);

        // UNUSED
        $keyGenerator = new KeyValueStoreKeyGenerator();
        $dataPoolReader = new DataPoolReader($keyValueStore, $keyGenerator);

        $pageKeyGenerator = new PageKeyGenerator($environment);

        $pageBuilder = new PageBuilder($pageKeyGenerator, $dataPoolReader);
        $page = $pageBuilder->buildPage($url);

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
        $keyValueStore->set('_product1_1_0', '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>');
        $keyValueStore->set('_product1_1_0_l', json_encode(['head', 'body']));
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
