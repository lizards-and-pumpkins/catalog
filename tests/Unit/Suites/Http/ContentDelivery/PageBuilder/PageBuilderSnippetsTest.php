<?php


namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;

use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilderSnippets;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\Import\Exception\InvalidSnippetContentException;
use LizardsAndPumpkins\Http\ContentDelivery\Exception\NonExistingSnippetException;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\Exception\PageContentBuildAlreadyTriggeredException;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilderSnippets
 */
class PageBuilderSnippetsTest extends \PHPUnit_Framework_TestCase
{
    private $testKey = 'a-key';

    private $testCode = 'a-code';

    private $testContent = 'some content';

    /**
     * @var PageBuilderSnippets
     */
    private $pageSnippets;

    protected function setUp()
    {
        $codeToKeyMap = [$this->testCode => $this->testKey];
        $keyToContentMap = [$this->testKey => $this->testContent];
        $containers = [];
        $this->pageSnippets = PageBuilderSnippets::fromCodesAndContent($codeToKeyMap, $keyToContentMap, $containers);
    }

    public function testItReturnsAPageSnippetInstance()
    {
        $codeToKeyMap = [];
        $keyToContentMap = [];
        $containers = [];
        $pageSnippets = PageBuilderSnippets::fromCodesAndContent($codeToKeyMap, $keyToContentMap, $containers);
        $this->assertInstanceOf(PageBuilderSnippets::class, $pageSnippets);
    }

    public function testItImplementsThePageSnippetsInterface()
    {
        $this->assertInstanceOf(PageSnippets::class, $this->pageSnippets);
    }

    public function testItReturnsTheNotLoadedSnippetCodes()
    {
        $codeToKeyMap = ['found' => 'found_key', 'missing' => 'missing_key'];
        $keyToContentMap = ['found_key' => 'found_content'];
        $containers = [];
        $pageSnippets = PageBuilderSnippets::fromCodesAndContent($codeToKeyMap, $keyToContentMap, $containers);
        $this->assertSame(['missing'], $pageSnippets->getNotLoadedSnippetCodes());
    }

    public function testItReturnsTheLoadedSnippetCodes()
    {
        $codeToKeyMap = ['found' => 'found_key', 'missing' => 'missing_key'];
        $keyToContentMap = ['found_key' => 'found_content'];
        $containers = [];
        $pageSnippets = PageBuilderSnippets::fromCodesAndContent($codeToKeyMap, $keyToContentMap, $containers);
        $this->assertSame(['found'], $pageSnippets->getSnippetCodes());
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
        $this->pageSnippets->updateSnippetByKey($this->testKey, 'new content');
        $this->assertSame('new content', $this->pageSnippets->getSnippetByKey($this->testKey));
    }

    public function testItThrowsAnExceptionIfTheGivenKeyIsNotKnown()
    {
        $this->expectException(NonExistingSnippetException::class);
        $this->expectExceptionMessage('The snippet key "not-existing-key" does not exist on the current page');
        $this->pageSnippets->updateSnippetByKey('not-existing-key', 'new content');
    }

    public function testItThrowsAnExceptionIfTheSnippetContentIsNotAStringWithKeySpec()
    {
        $this->expectException(InvalidSnippetContentException::class);
        $this->expectExceptionMessage('Invalid snippet content for the key "a-key" specified: expected string, got "NULL"');
        $this->pageSnippets->updateSnippetByKey('a-key', null);
    }

    public function testItUpdatesASnippetWithTheGivenCode()
    {
        $this->pageSnippets->updateSnippetByCode($this->testCode, 'new content');
        $this->assertSame('new content', $this->pageSnippets->getSnippetByKey($this->testKey));
    }

    public function testItThrowsAnExceptionWhenUpdatingANonExistingSnippet()
    {
        $this->expectException(NonExistingSnippetException::class);
        $this->expectExceptionMessage('The snippet code "not-existing-code" does not exist on the current page');
        $this->pageSnippets->updateSnippetByCode('not-existing-code', 'new content');
    }

    public function testItThrowsAnExceptionIfTheSnippetContentIsNotAStringWithCodeSpec()
    {
        $this->expectException(InvalidSnippetContentException::class);
        $this->expectExceptionMessage(
            'Invalid snippet content for the code "a-code" specified: expected string, got "integer"'
        );
        $this->pageSnippets->updateSnippetByCode($this->testCode, 123);
    }

    public function testItThrowsAnExceptionIfThePageIsBuiltTwice()
    {
        $this->expectException(PageContentBuildAlreadyTriggeredException::class);
        $this->expectExceptionMessage('The method buildPageContent() may only be called once an an instance');
        $this->pageSnippets->buildPageContent($this->testCode);
        $this->pageSnippets->buildPageContent($this->testCode);
    }

    public function testItReturnsTrueIfASnippetIsPresent()
    {
        $this->assertTrue($this->pageSnippets->hasSnippetCode($this->testCode));
    }

    public function testItReturnsFalseIfASnippetIsNotPresent()
    {
        $this->assertFalse($this->pageSnippets->hasSnippetCode('not-present-code'));
    }

    public function testItDoesNotDependOnMapSortOrder()
    {
        $codeToKeyMap = ['code-a' => 'key-a', 'code-b' => 'key-b', 'root' => 'root'];
        $keyToContentMap = ['key-b' => 'BBB', 'key-a' => 'AAA', 'root' => '{{snippet code-a}}{{snippet code-b}}'];
        $containers = [];
        $pageSnippets = PageBuilderSnippets::fromCodesAndContent($codeToKeyMap, $keyToContentMap, $containers);
        $result = $pageSnippets->buildPageContent('root');
        $this->assertSame('AAABBB', $result);
    }
}
