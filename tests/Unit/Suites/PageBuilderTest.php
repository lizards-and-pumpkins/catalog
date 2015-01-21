<?php
namespace Brera;

use Brera\Http\HttpUrl;
use Brera\KeyValue\DataPoolReader;

class PageBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageBuilder
     */
    private $pageBuilder;
    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataPoolReader;

    protected function setUp()
    {
        $url = HttpUrl::fromString('http://example.com/product.html');

        $environment = $this->getMock(Environment::class);
        $environment->expects($this->any())->method('getVersion')
            ->willReturn('1');

        $this->dataPoolReader = $this->getMockBuilder(DataPoolReader::class)
            ->disableOriginalConstructor()->getMock();

        $this->pageBuilder = new PageBuilder(
            $url, $environment, $this->dataPoolReader
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
        $this->dataPoolReader->expects($this->any())->method('get')
            ->willReturn($pageContent);

        $page = $this->pageBuilder->buildPage();
        $this->assertEquals($pageContent, $page->getBody());
    }

}
