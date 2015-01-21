<?php
namespace Brera;

use Brera\Http\HttpUrl;
use Brera\KeyValue\DataPoolReader;
use Brera\KeyValue\InMemory\InMemoryKeyValueStore;
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

        $pageKeyGenerator = new PageKeyGenerator();

        $pageBuilder = new PageBuilder($pageKeyGenerator, $dataPoolReader);
        $page = $pageBuilder->buildPage($url, $environment);

        // get the body and compare
        $body = $page->getBody();
        $expected = '<html><head><title>Mein Titel!</title></head><body><h1>Headline</h1></body></html>';

        $this->assertEquals($expected, $body);
    }

    /**
     * @param $keyValueStore
     */
    private function addBaseSnippetAndListToKeyValueStorage($keyValueStore)
    {
        $keyValueStore->set('_product1_1_0', '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>');
        $keyValueStore->set('_product1_1_0_l', json_encode(['head', 'body']));
    }

    /**
     * @param $keyValueStore
     */
    private function addSnippetsForReplacementToTheKeyValueStorage($keyValueStore)
    {
        $keyValueStore->set('head', '<title>Mein Titel!</title>');
        $keyValueStore->set('body', '<h1>Headline</h1>');
    }
}
