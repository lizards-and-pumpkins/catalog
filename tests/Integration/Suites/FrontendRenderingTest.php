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

        // add base snippet and list to the key value store
        $keyValueStore->set('_product1_1_0', '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>');
        $keyValueStore->set('_product1_1_0_l', json_encode(['head', 'body']));

        // add snippets for replacement to the key value stire
        $keyValueStore->set('head', '<title>Mein Titel!</title>');
        $keyValueStore->set('body', '<h1>Headline</h1>');

        // UNUSED
        $keyGenerator = new KeyValueStoreKeyGenerator();
        $dataPoolReader = new DataPoolReader($keyValueStore, $keyGenerator);

        // create a page builder
        $pageBuilder = new PageBuilder($url, $environment, $dataPoolReader);
        $page = $pageBuilder->buildPage();

        // get the body and compare
        $body = $page->getBody();
        $expected = '<html><head><title>Mein Titel!</title></head><body><h1>Headline</h1></body></html>';

        $this->assertEquals($expected, $body);
    }
}
