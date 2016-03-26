<?php


namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder;

use LizardsAndPumpkins\Import\Exception\InvalidSnippetContentException;
use LizardsAndPumpkins\Http\ContentDelivery\Exception\NonExistingSnippetException;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\Exception\PageContentBuildAlreadyTriggeredException;

class PageBuilderSnippets implements PageSnippets
{
    private static $placeholderTemplateString = '{{snippet %s}}';

    /**
     * @var string[]
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
    public static function fromCodesAndContent(array $codeToKeyMap, array $keyToContentMap, array $containerSnippets)
    {
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
    private static function sortKeyToContentByCodeToKeymap(array $codeToKeyMap, array $keyToContentMap)
    {
        return array_reduce($codeToKeyMap, function (array $carry, $key) use ($keyToContentMap) {
            return isset($keyToContentMap[$key]) ?
                array_merge($carry, [$key => $keyToContentMap[$key]]) :
                $carry;
        }, []);
    }

    /**
     * @param string $rootSnippetCode
     * @return string
     */
    public function buildPageContent($rootSnippetCode)
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
    public function getNotLoadedSnippetCodes()
    {
        return array_reduce(array_keys($this->codeToKeyMap), function (array $missingCodes, $code) {
            $key = $this->codeToKeyMap[$code];
            return isset($this->keyToContentMap[$key]) ?
                $missingCodes :
                array_merge($missingCodes, [$code]);
        }, []);
    }

    /**
     * @return string[]
     */
    public function getSnippetCodes()
    {
        if (!isset($this->memoizedLoadedSnippetCodes)) {
            $this->memoizedLoadedSnippetCodes = array_keys(array_filter($this->codeToKeyMap, function ($key) {
                return isset($this->keyToContentMap[$key]);
            }));
        }
        return $this->memoizedLoadedSnippetCodes;
    }

    /**
     * @param string $rootSnippetCode
     * @return string[]
     */
    private function getLoadedChildSnippetCodes($rootSnippetCode)
    {
        return array_filter($this->getSnippetCodes(), function ($code) use ($rootSnippetCode) {
            return $code !== $rootSnippetCode;
        });
    }

    /**
     * @param string $rootSnippetCode
     * @return string[]
     */
    private function separateRootAndChildSnippets($rootSnippetCode)
    {
        $rootSnippetKey = $this->codeToKeyMap[$rootSnippetCode];
        $rootSnippet = $this->getSnippetByKey($rootSnippetKey);
        $childSnippets = array_diff_key($this->keyToContentMap, [$rootSnippetKey => $rootSnippet]);
        return [$rootSnippet, $childSnippets];
    }

    /**
     * @param string $snippetKey
     * @return string
     */
    public function getSnippetByKey($snippetKey)
    {
        if (!array_key_exists($snippetKey, $this->keyToContentMap)) {
            throw new NonExistingSnippetException($this->formatSnippetNotAvailableErrorMessage($snippetKey));
        }
        return $this->keyToContentMap[$snippetKey];
    }

    /**
     * @param string $snippetKey
     * @return string string
     */
    private function formatSnippetNotAvailableErrorMessage($snippetKey)
    {
        return sprintf('Snippet not available (key "%s")', $snippetKey);
    }

    /**
     * @param string[] $snippetCodes
     * @return string[]
     */
    private function buildPlaceholdersFromCodes(array $snippetCodes)
    {
        return array_map([$this, 'buildPlaceholderFromCode'], $snippetCodes);
    }

    /**
     * @param string $code
     * @return string
     */
    private function buildPlaceholderFromCode($code)
    {
        // TODO delegate placeholder creation (and also use the delegate during import)
        /** @see LizardsAndPumpkins\Renderer\BlockRenderer::getBlockPlaceholder() * */

        return sprintf(static::$placeholderTemplateString, $code);
    }

    /**
     * @param string $content
     * @param string[] $placeholders
     * @param string[] $snippets
     * @return string
     */
    private function injectSnippetsIntoContent($content, array $placeholders, $snippets)
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
    private function replaceAsLongAsSomethingIsReplaced($content, array $placeholders, $snippets)
    {
        do {
            $content = str_replace($placeholders, $snippets, $content, $count);
        } while ($count);

        return $content;
    }

    /**
     * @param string $content
     * @return string
     */
    private function removePlaceholders($content)
    {
        $pattern = $this->buildPlaceholderFromCode('[^}]*');
        return preg_replace('/' . $pattern . '/', '', $content);
    }

    /**
     * @param string $snippetKey
     * @param string $content
     */
    public function updateSnippetByKey($snippetKey, $content)
    {
        if (!isset($this->keyToContentMap[$snippetKey])) {
            throw $this->createNonExistingSnippetException('key', $snippetKey);
        }
        $this->validateContent('key', $snippetKey, $content);
        $this->keyToContentMap[$snippetKey] = $content;
    }

    /**
     * @param string $snippetCode
     * @param string $content
     */
    public function updateSnippetByCode($snippetCode, $content)
    {
        if (!isset($this->codeToKeyMap[$snippetCode])) {
            throw $this->createNonExistingSnippetException('code', $snippetCode);
        }
        $this->validateContent('code', $snippetCode, $content);
        $this->updateSnippetByKey($this->codeToKeyMap[$snippetCode], $content);
    }

    /**
     * @param string $snippetCode
     * @return string
     */
    public function getSnippetByCode($snippetCode)
    {
        return $this->getSnippetByKey($this->codeToKeyMap[$snippetCode]);
    }

    /**
     * @param string $specType
     * @param string $snippetSpec
     * @param mixed $content
     */
    private function validateContent($specType, $snippetSpec, $content)
    {
        if (!is_string($content)) {
            $message = sprintf(
                'Invalid snippet content for the %s "%s" specified: expected string, got "%s"',
                $specType,
                $snippetSpec,
                gettype($content)
            );
            throw new InvalidSnippetContentException($message);
        }
    }

    /**
     * @param string $specType
     * @param string $snippetSpec
     * @return NonExistingSnippetException
     */
    private function createNonExistingSnippetException($specType, $snippetSpec)
    {
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

    /**
     * @param string $snippetCode
     * @return bool
     */
    public function hasSnippetCode($snippetCode)
    {
        return isset($this->codeToKeyMap[$snippetCode]) && $this->hasSnippetKey($this->codeToKeyMap[$snippetCode]);
    }

    /**
     * @param string $snippetKey
     * @return bool
     */
    private function hasSnippetKey($snippetKey)
    {
        return isset($this->keyToContentMap[$snippetKey]);
    }

    /**
     * @param string[] $containers
     * @return string[]
     */
    private static function buildContainerCodeToContentMap(array $containers)
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
    private static function getContainerContentPlaceholders(array $containerSnippets)
    {
        return array_reduce($containerSnippets, function ($carry, $snippetCode) {
            return $carry . sprintf(static::$placeholderTemplateString, $snippetCode);
        }, '');
    }
}
