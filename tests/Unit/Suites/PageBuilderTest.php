<?php
namespace Brera;

use Brera\KeyValue\KeyValueStore;

class PageBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageBuilder
     */
    private $pageBuilder;
    /**
     * @var KeyValueStore|\PHPUnit_Framework_MockObject_MockObject
     */
    private $keyValueStorage;

    protected function setUp()
    {
        $url = 'http://localhost/product.html';

        $environment = $this->getMock(Environment::class);
        $environment->expects($this->any())->method('getVersion')
            ->willReturn('1');

        $this->keyValueStorage = $this->getMock(KeyValueStore::class);

        $this->pageBuilder = new PageBuilder(
            $url, $environment, $this->keyValueStorage
        );
    }

    /**
     * @test
     */
    public function itShouldReturnAPage()
    {
        $this->assertInstanceOf(Page::class, $this->pageBuilder->buildPage());
    }

    /**
     * @test
     */
    public function itShouldGetFirstSnippet()
    {
        $pageContent = 'my page';

        // TODO we should check here the key which is passed
        $this->keyValueStorage->expects($this->any())->method('get')
            ->willReturn($pageContent);

        $page = $this->pageBuilder->buildPage();
        $this->assertEquals($pageContent, $page->getBody());
    }

}
