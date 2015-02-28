<?php

namespace Brera;

/**
 * @covers \Brera\PageMetaInfoSnippetContent
 */
class PageMetaInfoSnippetContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageMetaInfoSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var string
     */
    private $rootSnippetCode = 'root-snippet-code';

    /**
     * @var string
     */
    private $sourceId = '123';

    protected function setUp()
    {
        $this->pageMetaInfo = PageMetaInfoSnippetContent::create(
            $this->sourceId,
            $this->rootSnippetCode,
            [$this->rootSnippetCode]
        );
    }

    /**
     * @test
     */
    public function itShouldReturnArray()
    {
        $this->assertInternalType('array', $this->pageMetaInfo->getInfo());
    }

    /**
     * @test
     */
    public function itShouldContainTheExpectedArrayKeysInTheJsonContent()
    {
        $keys = [
            PageMetaInfoSnippetContent::KEY_SOURCE_ID,
            PageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE,
            PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES
        ];
        foreach ($keys as $key) {
            $this->assertTrue(
                array_key_exists($key, $this->pageMetaInfo->getInfo()),
                sprintf('The expected key "%s" is not set on the page meta info array', $key)
            );
        }
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function itShouldThrowAnExceptionIfTheSourceIdIsNotScalar()
    {
        PageMetaInfoSnippetContent::create([], 'test', []);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function itShouldThrowAnExceptionIfTheRootSnippetCodeIsNoString()
    {
        PageMetaInfoSnippetContent::create(123, 1.0, []);
    }

    /**
     * @test
     */
    public function itShouldAddTheRootSnippetCodeToTheSnippetCodeListIfNotPresent()
    {
        $rootSnippetCode = 'root-snippet-code';
        $pageMetaInfo = PageMetaInfoSnippetContent::create('123', $rootSnippetCode, []);
        $this->assertContains(
            $rootSnippetCode,
            $pageMetaInfo->getInfo()[PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES]
        );
    }

    /**
     * @test
     */
    public function itShouldHaveAFromJsonConstructor()
    {
        $pageMetaInfo = PageMetaInfoSnippetContent::fromJson(json_encode($this->pageMetaInfo->getInfo()));
        $this->assertInstanceOf(PageMetaInfoSnippetContent::class, $pageMetaInfo);
    }
    
    /**
     * @test
     * @expectedException \OutOfBoundsException
     */
    public function itShouldThrowAnExceptionInCaseOfJsonErrors()
    {
        PageMetaInfoSnippetContent::fromJson('malformed-json');
    }

    /**
     * @test
     * @dataProvider pageInfoArrayKeyProvider
     * @param string $key
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Missing key in input JSON
     */
    public function itShouldThrowAnExceptionIfARequiredKeyIsMissing($key)
    {
        $pageInfo = $this->pageMetaInfo->getInfo();
        unset($pageInfo[$key]);
        $pageMetaInfo = PageMetaInfoSnippetContent::fromJson(json_encode($pageInfo));
        $this->assertInstanceOf(PageMetaInfoSnippetContent::class, $pageMetaInfo);
    }

    /**
     * @return array[]
     */
    public function pageInfoArrayKeyProvider()
    {
        return [
            [PageMetaInfoSnippetContent::KEY_SOURCE_ID],
            [PageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE],
            [PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES],
        ];
    }

    /**
     * @test
     */
    public function itShouldReturnTheSourceId()
    {
        $this->assertEquals($this->sourceId, $this->pageMetaInfo->getSourceId());
    }

    /**
     * @test
     */
    public function itShouldReturnTheRootSnippetCode()
    {
        $this->assertEquals($this->rootSnippetCode, $this->pageMetaInfo->getRootSnippetCode());
    }

    /**
     * @test
     */
    public function itShouldReturnThePageSnippetCodeList()
    {
        $this->assertInternalType('array', $this->pageMetaInfo->getPageSnippetCodes());
    }
}
