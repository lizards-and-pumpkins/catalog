<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder;

use LizardsAndPumpkins\Http\ContentDelivery\Exception\NonExistingSnippetException;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\Exception\PageContentBuildAlreadyTriggeredException;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\Exception\MaxSnippetNestingLevelExceededException;
use LizardsAndPumpkins\Import\SnippetCode;

class PageBuilderSnippets implements PageSnippets
{
    const MAX_SNIPPET_DEPTH = 50;

    private static $placeholderTemplateString = '{{snippet %s}}';

    /**
     * @var SnippetCode[]
     */
    private $memoizedLoadedSnippetCodes;

    /**
     * @var string[]
     */
    private $codeToKeyMap;

    /**
     * @var string[]
     */
    private $keyToContentMap;

    private $pageWasBuilt = false;

    /**
     * @param string[] $codeToKeyMap
     * @param string[] $keyToContentMap
     */
    private function __construct(array $codeToKeyMap, array $keyToContentMap)
    {
        $this->codeToKeyMap = $codeToKeyMap;
        $this->keyToContentMap = $keyToContentMap;
    }

    /**
     * @param string[] $codeToKeyMap
     * @param string[] $keyToContentMap
     * @param string[] $containerSnippets
     * @return PageBuilderSnippets
     */
    public static function fromCodesAndContent(
        array $codeToKeyMap,
        array $keyToContentMap,
        array $containerSnippets
    ): PageBuilderSnippets {
        $containerKeys = array_keys($containerSnippets);
        $containerCodeToContentMap = self::buildContainerCodeToContentMap($containerSnippets);
        $combinedCodeToKeyMap = array_merge($codeToKeyMap, array_combine($containerKeys, $containerKeys));
        $combinedKeyToContentMap = array_merge($keyToContentMap, $containerCodeToContentMap);
        $sortedKeyToContentMap = self::sortKeyToContentByCodeToKeymap($combinedCodeToKeyMap, $combinedKeyToContentMap);
        return new self($combinedCodeToKeyMap, $sortedKeyToContentMap);
    }

    /**
     * @param string[] $codeToKeyMap
     * @param string[] $keyToContentMap
     * @return string[]
     */
    private static function sortKeyToContentByCodeToKeymap(array $codeToKeyMap, array $keyToContentMap): array
    {
        return array_reduce($codeToKeyMap, function (array $carry, $key) use ($keyToContentMap) {
            return isset($keyToContentMap[(string) $key]) ?
                array_merge($carry, [(string) $key => $keyToContentMap[(string) $key]]) :
                $carry;
        }, []);
    }

    public function buildPageContent(SnippetCode $rootSnippetCode): string
    {
        $this->guardAgainstMultipleInvocations();
        list($rootSnippet, $childSnippets) = $this->separateRootAndChildSnippets($rootSnippetCode);
        $childSnippetsCodes = $this->getLoadedChildSnippetCodes($rootSnippetCode);
        $placeholders = $this->buildPlaceholdersFromCodes($childSnippetsCodes);
        return $this->injectSnippetsIntoContent($rootSnippet, $placeholders, $childSnippets);
    }

    /**
     * @return string[]
     */
    public function getNotLoadedSnippetCodes(): array
    {
        return array_reduce(array_keys($this->codeToKeyMap), function (array $missingCodes, $code) {
            $key = $this->codeToKeyMap[$code];
            return isset($this->keyToContentMap[$key]) ?
                $missingCodes :
                array_merge($missingCodes, [$code]);
        }, []);
    }

    /**
     * @return SnippetCode[]
     */
    public function getSnippetCodes(): array
    {
        if (! isset($this->memoizedLoadedSnippetCodes)) {
            $codeStrings = array_keys($this->codeToKeyMap);
            $this->memoizedLoadedSnippetCodes = array_reduce($codeStrings, function ($carry, string $codeString) {
                $key = (string) $this->codeToKeyMap[$codeString];
                if (! isset($this->keyToContentMap[$key])) {
                    return $carry;
                }

                return array_merge($carry, [new SnippetCode($codeString)]);
            }, []);
        }

        return $this->memoizedLoadedSnippetCodes;
    }

    /**
     * @param SnippetCode $rootSnippetCode
     * @return string[]
     */
    private function getLoadedChildSnippetCodes(SnippetCode $rootSnippetCode): array
    {
        return array_filter($this->getSnippetCodes(), function (SnippetCode $code) use ($rootSnippetCode) {
            return (string) $code !== (string) $rootSnippetCode;
        });
    }

    /**
     * @param SnippetCode $rootSnippetCode
     * @return string[]
     */
    private function separateRootAndChildSnippets(SnippetCode $rootSnippetCode): array
    {
        $rootSnippetKey = $this->codeToKeyMap[(string) $rootSnippetCode];
        $rootSnippet = $this->getSnippetByKey($rootSnippetKey);
        $childSnippets = array_diff_key($this->keyToContentMap, [$rootSnippetKey => $rootSnippet]);
        return [$rootSnippet, $childSnippets];
    }

    public function getSnippetByKey(string $snippetKey): string
    {
        if (! array_key_exists($snippetKey, $this->keyToContentMap)) {
            throw new NonExistingSnippetException($this->formatSnippetNotAvailableErrorMessage($snippetKey));
        }
        return $this->keyToContentMap[$snippetKey];
    }

    private function formatSnippetNotAvailableErrorMessage(string $snippetKey): string
    {
        return sprintf('Snippet not available (key "%s")', $snippetKey);
    }

    /**
     * @param string[] $snippetCodes
     * @return string[]
     */
    private function buildPlaceholdersFromCodes(array $snippetCodes): array
    {
        return array_map([$this, 'buildPlaceholderFromCode'], $snippetCodes);
    }

    /**
     * @param string $code
     * @return string
     */
    private function buildPlaceholderFromCode(string $code): string
    {
        // TODO delegate placeholder creation (and also use the delegate during import)
        /** @see \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer::getBlockPlaceholder() * */

        return sprintf(static::$placeholderTemplateString, $code);
    }

    /**
     * @param string $content
     * @param string[] $placeholders
     * @param string[] $snippets
     * @return string
     */
    private function injectSnippetsIntoContent(string $content, array $placeholders, array $snippets): string
    {
        return $this->removePlaceholders(
            $this->replaceAsLongAsSomethingIsReplaced($content, $placeholders, $snippets)
        );
    }

    /**
     * @param string $content
     * @param string[] $placeholders
     * @param string[] $snippets
     * @return string
     */
    private function replaceAsLongAsSomethingIsReplaced(string $content, array $placeholders, $snippets): string
    {
        $recursionCounter = 0;
        do {
            $content = str_replace($placeholders, $snippets, $content, $count);
            $recursionCounter++;
            if ($recursionCounter > self::MAX_SNIPPET_DEPTH) {
                throw new MaxSnippetNestingLevelExceededException(sprintf(
                    'Snippets are nested deeper than %s levels or a loop is inside snippets.',
                    self::MAX_SNIPPET_DEPTH
                ));
            }
        } while ($count);

        return $content;
    }

    private function removePlaceholders(string $content): string
    {
        $pattern = $this->buildPlaceholderFromCode('[^}]*');
        return preg_replace('/' . $pattern . '/', '', $content);
    }

    public function updateSnippetByKey(string $snippetKey, string $content)
    {
        if (! isset($this->keyToContentMap[$snippetKey])) {
            throw $this->createNonExistingSnippetException('key', $snippetKey);
        }
        $this->keyToContentMap[$snippetKey] = $content;
    }

    public function updateSnippetByCode(SnippetCode $snippetCode, string $content)
    {
        if (! isset($this->codeToKeyMap[(string) $snippetCode])) {
            throw $this->createNonExistingSnippetException('code', (string) $snippetCode);
        }
        $this->updateSnippetByKey($this->codeToKeyMap[(string) $snippetCode], $content);
    }

    public function getSnippetByCode(SnippetCode $snippetCode): string
    {
        return $this->getSnippetByKey($this->codeToKeyMap[(string) $snippetCode]);
    }

    private function createNonExistingSnippetException(
        string $specType,
        string $snippetSpec
    ): NonExistingSnippetException {
        $message = sprintf('The snippet %s "%s" does not exist on the current page', $specType, $snippetSpec);
        return new NonExistingSnippetException($message);
    }

    private function guardAgainstMultipleInvocations()
    {
        if ($this->pageWasBuilt) {
            $message = 'The method buildPageContent() may only be called once an an instance';
            throw new PageContentBuildAlreadyTriggeredException($message);
        }
        $this->pageWasBuilt = true;
    }

    public function hasSnippetCode(SnippetCode $snippetCode): bool
    {
        return isset($this->codeToKeyMap[(string) $snippetCode]) &&
               $this->hasSnippetKey($this->codeToKeyMap[(string) $snippetCode]);
    }

    private function hasSnippetKey(string $snippetKey): bool
    {
        return isset($this->keyToContentMap[$snippetKey]);
    }

    /**
     * @param string[] $containers
     * @return string[]
     */
    private static function buildContainerCodeToContentMap(array $containers): array
    {
        return array_reduce(array_keys($containers), function ($carry, $containerCode) use ($containers) {
            $containedSnippets = $containers[$containerCode];
            return array_merge($carry, [$containerCode => self::getContainerContentPlaceholders($containedSnippets)]);
        }, []);
    }

    /**
     * @param string[] $containerSnippets
     * @return string
     */
    private static function getContainerContentPlaceholders(array $containerSnippets): string
    {
        return array_reduce($containerSnippets, function ($carry, $snippetCode) {
            return $carry . sprintf(static::$placeholderTemplateString, $snippetCode);
        }, '');
    }
}
