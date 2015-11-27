<?php


namespace LizardsAndPumpkins\ContentDelivery\PageBuilder;

use LizardsAndPumpkins\ContentDelivery\PageBuilder\Exception\InvalidSnippetContentException;
use LizardsAndPumpkins\ContentDelivery\PageBuilder\Exception\NonExistingSnippetException;
use LizardsAndPumpkins\Exception\InvalidPageMetaSnippetException;

class PageSnippets
{
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
     * @return PageSnippets
     */
    public static function fromKeyCodeAndContent(array $codeToKeyMap, array $keyToContentMap)
    {
        return new self($codeToKeyMap, $keyToContentMap);
    }

    /**
     * @param string $rootSnippetCode
     * @return string
     */
    public function buildPageContent($rootSnippetCode)
    {
        list($rootSnippet, $childSnippets) = $this->separateRootAndChildSnippets($rootSnippetCode);
        $childSnippetsCodes = $this->getLoadedChildSnippetCodes($rootSnippetCode);
        $childSnippetPlaceholdersToContentMap = $this->mergePlaceholderAndSnippets($childSnippetsCodes, $childSnippets);
        return $this->injectSnippetsIntoContent($rootSnippet, $childSnippetPlaceholdersToContentMap);
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
    public function getLoadedSnippetCodes()
    {
        if (! isset($this->memoizedLoadedSnippetCodes)) {
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
        return array_filter($this->getLoadedSnippetCodes(), function ($code) use ($rootSnippetCode) {
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
            throw new InvalidPageMetaSnippetException($this->formatSnippetNotAvailableErrorMessage($snippetKey));
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
     * @param string[] $snippets
     * @return string[]
     */
    private function mergePlaceholderAndSnippets(array $snippetCodes, array $snippets)
    {
        $snippetPlaceholders = $this->buildPlaceholdersFromCodes($snippetCodes);
        return array_combine($snippetPlaceholders, $snippets);
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

        return sprintf('{{snippet %s}}', $code);
    }

    /**
     * @param string $content
     * @param string[] $snippetPlaceholdersToContentMap
     * @return string
     */
    private function injectSnippetsIntoContent($content, array $snippetPlaceholdersToContentMap)
    {
        return $this->removePlaceholders(
            $this->replaceAsLongAsSomethingIsReplaced($content, $snippetPlaceholdersToContentMap)
        );
    }

    /**
     * @param string $content
     * @param string[] $snippets
     * @return string
     */
    private function replaceAsLongAsSomethingIsReplaced($content, array $snippets)
    {
        do {
            $content = str_replace(array_keys($snippets), array_values($snippets), $content, $count);
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
     * @param string $snippetCode
     * @return string
     */
    public function getKeyByCode($snippetCode)
    {
        return $this->codeToKeyMap[$snippetCode];
    }

    /**
     * @param string $snippetKey
     * @param string $content
     */
    public function updateSnippetByKey($snippetKey, $content)
    {
        if (! isset($this->keyToContentMap[$snippetKey])) {
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
        if (! isset($this->codeToKeyMap[$snippetCode])) {
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
}
