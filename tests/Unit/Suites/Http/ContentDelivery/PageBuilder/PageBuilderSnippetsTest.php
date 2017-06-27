<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder;

use LizardsAndPumpkins\Http\ContentDelivery\Exception\NonExistingSnippetException;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\Exception\MaxSnippetNestingLevelExceededException;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\Exception\PageContentBuildAlreadyTriggeredException;
use LizardsAndPumpkins\Import\SnippetCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilderSnippets
 * @uses   \LizardsAndPumpkins\Import\SnippetCode
 */
class PageBuilderSnippetsTest extends TestCase
{
    private $testKey = 'a-key';

    /**
     * @var SnippetCode
     */
    private $testCode;

    private $testContent = 'some content';

    /**
     * @var PageBuilderSnippets
     */
    private $pageSnippets;

    protected function setUp()
    {
        $this->testCode = new SnippetCode('a-code');
        $codeToKeyMap = [(string) $this->testCode => $this->testKey];
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
        $this->assertEquals([new SnippetCode('found')], $pageSnippets->getSnippetCodes());
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
        $this->expectException(\TypeError::class);
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
        $this->pageSnippets->updateSnippetByCode(new SnippetCode('not-existing-code'), 'new content');
    }

    public function testItThrowsAnExceptionIfTheSnippetContentIsNotAStringWithCodeSpec()
    {
        $this->expectException(\TypeError::class);
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
        $this->assertFalse($this->pageSnippets->hasSnippetCode(new SnippetCode('not-present-code')));
    }

    public function testItDoesNotDependOnMapSortOrder()
    {
        $codeToKeyMap = ['code-a' => 'key-a', 'code-b' => 'key-b', 'root' => 'root'];
        $keyToContentMap = ['key-b' => 'BBB', 'key-a' => 'AAA', 'root' => '{{snippet code-a}}{{snippet code-b}}'];
        $containers = [];
        $pageSnippets = PageBuilderSnippets::fromCodesAndContent($codeToKeyMap, $keyToContentMap, $containers);
        $result = $pageSnippets->buildPageContent(new SnippetCode('root'));
        $this->assertSame('AAABBB', $result);
    }

    public function testThrowsExceptionIfALoopIsFound()
    {
        $this->expectException(MaxSnippetNestingLevelExceededException::class);
        $this->expectExceptionMessage('Snippets are nested deeper than 50 levels or a loop is inside snippets.');
        $codeToKeyMap = ['code-a' => 'key-a', 'code-b' => 'key-b', 'root' => 'root'];
        $keyToContentMap = [
            'key-b' => '{{snippet code-a}}',
            'key-a' => '{{snippet code-b}}',
            'root'  => '{{snippet code-a}}',
        ];
        $containers = [];
        $pageSnippets = PageBuilderSnippets::fromCodesAndContent($codeToKeyMap, $keyToContentMap, $containers);
        $pageSnippets->buildPageContent(new SnippetCode('root'));
    }

    public function testThrowsExceptionIfNestingIsTooDepp()
    {
        $this->expectException(MaxSnippetNestingLevelExceededException::class);
        $this->expectExceptionMessage('Snippets are nested deeper than 50 levels or a loop is inside snippets.');

        $codeToKeyMap = ['root' => 'root'];
        $keyToContentMap = ['root' => '{{snippet code-1}}'];
        for ($i = PageBuilderSnippets::MAX_SNIPPET_DEPTH; $i >= 1; $i--) {
            $codeToKeyMap['code-' . $i] = 'key-' . $i;
            $next = $i + 1;
            $keyToContentMap['key-' . $i] = "{{snippet code-{$next}}}";
        }

        $containers = [];
        $pageSnippets = PageBuilderSnippets::fromCodesAndContent($codeToKeyMap, $keyToContentMap, $containers);
        $pageSnippets->buildPageContent(new SnippetCode('root'));
    }
}
