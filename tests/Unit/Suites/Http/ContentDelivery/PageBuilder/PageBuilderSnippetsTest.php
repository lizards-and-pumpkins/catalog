<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder;

use LizardsAndPumpkins\Http\ContentDelivery\Exception\NonExistingSnippetException;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\Exception\MaxSnippetNestingLevelExceededException;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\Exception\PageContentBuildAlreadyTriggeredException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilderSnippets
 */
class PageBuilderSnippetsTest extends TestCase
{
    private $testKey = 'a-key';

    private $testCode = 'a-code';

    private $testContent = 'some content';

    /**
     * @var PageBuilderSnippets
     */
    private $pageSnippets;

    final protected function setUp(): void
    {
        $codeToKeyMap = [$this->testCode => $this->testKey];
        $keyToContentMap = [$this->testKey => $this->testContent];
        $containers = [];
        $this->pageSnippets = PageBuilderSnippets::fromCodesAndContent($codeToKeyMap, $keyToContentMap, $containers);
    }

    public function testItReturnsAPageSnippetInstance(): void
    {
        $codeToKeyMap = [];
        $keyToContentMap = [];
        $containers = [];
        $pageSnippets = PageBuilderSnippets::fromCodesAndContent($codeToKeyMap, $keyToContentMap, $containers);
        $this->assertInstanceOf(PageBuilderSnippets::class, $pageSnippets);
    }

    public function testItImplementsThePageSnippetsInterface(): void
    {
        $this->assertInstanceOf(PageSnippets::class, $this->pageSnippets);
    }

    public function testItReturnsTheNotLoadedSnippetCodes(): void
    {
        $codeToKeyMap = ['found' => 'found_key', 'missing' => 'missing_key'];
        $keyToContentMap = ['found_key' => 'found_content'];
        $containers = [];
        $pageSnippets = PageBuilderSnippets::fromCodesAndContent($codeToKeyMap, $keyToContentMap, $containers);
        $this->assertSame(['missing'], $pageSnippets->getNotLoadedSnippetCodes());
    }

    public function testItReturnsTheLoadedSnippetCodes(): void
    {
        $codeToKeyMap = ['found' => 'found_key', 'missing' => 'missing_key'];
        $keyToContentMap = ['found_key' => 'found_content'];
        $containers = [];
        $pageSnippets = PageBuilderSnippets::fromCodesAndContent($codeToKeyMap, $keyToContentMap, $containers);
        $this->assertSame(['found'], $pageSnippets->getSnippetCodes());
    }

    public function testItReturnsTheSnippetContentForAGivenKey(): void
    {
        $this->assertSame('some content', $this->pageSnippets->getSnippetByKey($this->testKey));
    }

    public function testItReturnsTheSnippetContentForAGivenCode(): void
    {
        $this->assertSame('some content', $this->pageSnippets->getSnippetByCode($this->testCode));
    }

    public function testItUpdatesASnippetWithTheGivenKey(): void
    {
        $this->pageSnippets->updateSnippetByKey($this->testKey, 'new content');
        $this->assertSame('new content', $this->pageSnippets->getSnippetByKey($this->testKey));
    }

    public function testItThrowsAnExceptionIfTheGivenKeyIsNotKnown(): void
    {
        $this->expectException(NonExistingSnippetException::class);
        $this->expectExceptionMessage('The snippet key "not-existing-key" does not exist on the current page');
        $this->pageSnippets->updateSnippetByKey('not-existing-key', 'new content');
    }

    public function testItThrowsAnExceptionIfTheSnippetContentIsNotAStringWithKeySpec(): void
    {
        $this->expectException(\TypeError::class);
        $this->pageSnippets->updateSnippetByKey('a-key', null);
    }

    public function testItUpdatesASnippetWithTheGivenCode(): void
    {
        $this->pageSnippets->updateSnippetByCode($this->testCode, 'new content');
        $this->assertSame('new content', $this->pageSnippets->getSnippetByKey($this->testKey));
    }

    public function testItThrowsAnExceptionWhenUpdatingANonExistingSnippet(): void
    {
        $this->expectException(NonExistingSnippetException::class);
        $this->expectExceptionMessage('The snippet code "not-existing-code" does not exist on the current page');
        $this->pageSnippets->updateSnippetByCode('not-existing-code', 'new content');
    }

    public function testItThrowsAnExceptionIfTheSnippetContentIsNotAStringWithCodeSpec(): void
    {
        $this->expectException(\TypeError::class);
        $this->pageSnippets->updateSnippetByCode($this->testCode, 123);
    }

    public function testItThrowsAnExceptionIfThePageIsBuiltTwice(): void
    {
        $this->expectException(PageContentBuildAlreadyTriggeredException::class);
        $this->expectExceptionMessage('The method buildPageContent() may only be called once an an instance');
        $this->pageSnippets->buildPageContent($this->testCode);
        $this->pageSnippets->buildPageContent($this->testCode);
    }

    public function testItReturnsTrueIfASnippetIsPresent(): void
    {
        $this->assertTrue($this->pageSnippets->hasSnippetCode($this->testCode));
    }

    public function testItReturnsFalseIfASnippetIsNotPresent(): void
    {
        $this->assertFalse($this->pageSnippets->hasSnippetCode('not-present-code'));
    }

    public function testItDoesNotDependOnMapSortOrder(): void
    {
        $codeToKeyMap = ['code-a' => 'key-a', 'code-b' => 'key-b', 'root' => 'root'];
        $keyToContentMap = ['key-b' => 'BBB', 'key-a' => 'AAA', 'root' => '{{snippet code-a}}{{snippet code-b}}'];
        $containers = [];
        $pageSnippets = PageBuilderSnippets::fromCodesAndContent($codeToKeyMap, $keyToContentMap, $containers);
        $result = $pageSnippets->buildPageContent('root');
        $this->assertSame('AAABBB', $result);
    }

    public function testThrowsExceptionIfALoopIsFound(): void
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
        $pageSnippets->buildPageContent('root');
    }

    public function testThrowsExceptionIfNestingIsTooDepp(): void
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
        $pageSnippets->buildPageContent('root');
    }
}
