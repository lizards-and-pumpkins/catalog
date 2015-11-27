<?php


namespace LizardsAndPumpkins\ContentDelivery\PageBuilder;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\PageBuilder\PageSnippets
 */
class PageSnippetsTest extends \PHPUnit_Framework_TestCase
{
    private $testKey = 'a-key';

    private $testCode = 'a-code';

    private $testContent = 'some content';

    /**
     * @var PageSnippets
     */
    private $pageSnippets;

    protected function setUp()
    {
        $codeToKeyMap = [$this->testCode => $this->testKey];
        $keyToContentMap = [$this->testKey => $this->testContent];
        $this->pageSnippets = PageSnippets::fromKeyCodeAndContent($codeToKeyMap, $keyToContentMap);
    }
    
    public function testItReturnsAPageSnippetInstance()
    {
        $codeToKeyMap = [];
        $keyToContentMap = [];
        $pageSnippets = PageSnippets::fromKeyCodeAndContent($codeToKeyMap, $keyToContentMap);
        $this->assertInstanceOf(PageSnippets::class, $pageSnippets);
    }

    public function testItReturnsTheNotLoadedSnippetCodes()
    {
        $codeToKeyMap = ['found' => 'found_key', 'missing' => 'missing_key'];
        $keyToContentMap = ['found_key' => 'found_content'];
        $pageSnippets = PageSnippets::fromKeyCodeAndContent($codeToKeyMap, $keyToContentMap);
        $this->assertSame(['missing'], $pageSnippets->getNotLoadedSnippetCodes());
    }

    public function testItReturnsTheLoadedSnippetCodes()
    {
        $codeToKeyMap = ['found' => 'found_key', 'missing' => 'missing_key'];
        $keyToContentMap = ['found_key' => 'found_content'];
        $pageSnippets = PageSnippets::fromKeyCodeAndContent($codeToKeyMap, $keyToContentMap);
        $this->assertSame(['found'], $pageSnippets->getLoadedSnippetCodes());
    }

    public function testItReturnsTheSnippetKeyForAGivenCode()
    {
        $this->assertSame($this->testKey, $this->pageSnippets->getKeyByCode($this->testCode));
    }

    public function testItReturnsTheSnippetContentForAGivenKey()
    {
        $this->assertSame('some content', $this->pageSnippets->getSnippetByKey($this->testKey));
    }

    public function testItReturnsTheSnippetContentForAGivenCode()
    {
        $this->assertSame('some content', $this->pageSnippets->getSnippetByCode($this->testCode));
    }

    public function testItUpdatesASnippetWithTheGivenKey()
    {
        $this->pageSnippets->setSnippetByKey($this->testKey, 'new content');
        $this->assertSame('new content', $this->pageSnippets->getSnippetByKey($this->testKey));
    }

    public function testItUpdatesASnippetWithTheGivenCode()
    {
        $this->pageSnippets->setSnippetByCode($this->testCode, 'new content');
        $this->assertSame('new content', $this->pageSnippets->getSnippetByKey($this->testKey));
    }
}
