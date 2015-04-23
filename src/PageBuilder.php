<?php

namespace Brera;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;

class PageBuilder
{
    private $rootSnippetCode;
    
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var string[]
     */
    private $snippetCodeToKeyMap = [];

    /**
     * @var string[]
     */
    private $snippetKeyToContentMap = [];
    
    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $keyGeneratorLocator;

    /**
     * @var Context
     */
    private $context;
    
    private $keyGeneratorParams;
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        DataPoolReader $dataPoolReader,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        Logger $logger
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->logger = $logger;
    }

    /**
     * @param PageMetaInfoSnippetContent $metaInfo
     * @param Context $context
     * @param mixed[] $keyGeneratorParams
     * @return Page
     */
    public function buildPage(PageMetaInfoSnippetContent $metaInfo, Context $context, array $keyGeneratorParams)
    {
        $this->context = $context;
        $this->keyGeneratorParams = $keyGeneratorParams;
        $this->initFromMetaInfo($metaInfo);
        $this->loadSnippets();
        $this->logMissingSnippets();
        $content = $this->buildPageContent();

        return new Page($content);
    }

    /**
     * @param string[] $snippetCodeToKeyMap
     * @param string[] $snippetKeyToContentMap
     */
    public function addSnippetsToPage(array $snippetCodeToKeyMap, array $snippetKeyToContentMap)
    {
        $this->snippetCodeToKeyMap = array_merge($this->snippetCodeToKeyMap, $snippetCodeToKeyMap);
        $this->snippetKeyToContentMap = array_merge($this->snippetKeyToContentMap, $snippetKeyToContentMap);
    }

    private function initFromMetaInfo(PageMetaInfoSnippetContent $metaInfo)
    {
        $this->rootSnippetCode = $metaInfo->getRootSnippetCode();
        $snippetCodes = $this->addRootSnippetCodeToPageSnippetCodesIfMissing($metaInfo->getPageSnippetCodes());
        $this->snippetCodeToKeyMap = array_merge($this->snippetCodeToKeyMap, array_combine(
            $snippetCodes,
            array_map([$this, 'tryToGetSnippetKey'], $snippetCodes)
        ));
    }
    /**
     * @param string $snippetCode
     * @return string
     */
    private function tryToGetSnippetKey($snippetCode)
    {
        try {
            $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);
            $keyForContext = $keyGenerator->getKeyForContext($this->context, $this->keyGeneratorParams);
        } catch (\Exception $e) {
            $keyForContext = '';
        }
        return $keyForContext;
    }

    private function loadSnippets()
    {
        $keys = $this->getSnippetKeysInContext();
        $this->snippetKeyToContentMap = array_merge(
            $this->snippetKeyToContentMap,
            $this->dataPoolReader->getSnippets($keys)
        );
    }

    /**
     * @return string[]
     */
    private function getSnippetKeysInContext()
    {
        return array_values($this->removeCodesThatCouldNotBeMappedToAKey($this->snippetCodeToKeyMap));
    }

    /**
     * @param string[] $snippetKeys
     * @return string[]
     */
    private function removeCodesThatCouldNotBeMappedToAKey(array $snippetKeys)
    {
        return array_filter($snippetKeys);
    }

    private function logMissingSnippets()
    {
        $missingSnippetCodes = $this->getNotLoadedSnippetCodes();
        if (count($missingSnippetCodes) > 0) {
            $this->logger->log(new MissingSnippetCodeMessage($missingSnippetCodes, ['context' => $this->context]));
        }
    }

    /**
     * @return string[]
     */
    private function getNotLoadedSnippetCodes()
    {
        $missingSnippetCodes = [];
        foreach ($this->snippetCodeToKeyMap as $code => $key) {
            if (!array_key_exists($key, $this->snippetKeyToContentMap)) {
                $missingSnippetCodes[] = $code;
            }
        }
        return $missingSnippetCodes;
    }
    
    /**
     * @return string
     */
    private function buildPageContent()
    {
        list($rootSnippet, $childSnippets) = $this->separateRootAndChildSnippets();
        $childSnippetsCodes = $this->getLoadedChildSnippetCodes();
        $childSnippetCodesToContentMap = $this->mergePlaceholderAndSnippets($childSnippetsCodes, $childSnippets);
        return $this->injectSnippetsIntoContent($rootSnippet, $childSnippetCodesToContentMap);
    }

    /**
     * @return string[]
     */
    private function separateRootAndChildSnippets()
    {
        $rootSnippetKey = $this->snippetCodeToKeyMap[$this->rootSnippetCode];
        $rootSnippet = $this->getSnippetByKey($rootSnippetKey);
        $childSnippets = array_diff_key($this->snippetKeyToContentMap, [$rootSnippetKey => $rootSnippet]);
        return [$rootSnippet, $childSnippets];
    }

    /**
     * @param string $snippetKey
     * @return string
     * @throws InvalidPageMetaSnippetException
     */
    private function getSnippetByKey($snippetKey)
    {
        if (!array_key_exists($snippetKey, $this->snippetKeyToContentMap)) {
            throw new InvalidPageMetaSnippetException($this->formatSnippetNotAvailableErrorMessage($snippetKey));
        }
        return $this->snippetKeyToContentMap[$snippetKey];
    }

    /**
     * @param string $snippetKey
     * @return string string
     */
    private function formatSnippetNotAvailableErrorMessage($snippetKey)
    {
        return sprintf(
            'Snippet not available (key "%s", context "%s")',
            $snippetKey,
            $this->context->getId()
        );
    }
    
    /**
     * @return string[]
     */
    private function getLoadedChildSnippetCodes()
    {
        return array_filter(array_keys($this->snippetCodeToKeyMap), function ($code) {
            return $code !== $this->rootSnippetCode &&
            array_key_exists($this->snippetCodeToKeyMap[$code], $this->snippetKeyToContentMap);
        });
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
        /** @see Brera\Renderer\BlockRenderer::getBlockPlaceholder() **/

        return sprintf('{{snippet %s}}', $code);
    }
    /**
     * @param string $content
     * @param string[] $snippets
     * @return string
     */
    private function injectSnippetsIntoContent($content, array $snippets)
    {
        return $this->removePlaceholders(
            $this->replaceAsLongAsSomethingIsReplaced($content, $snippets)
        );
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
     * @param string[] $snippetCodes
     * @return string[]
     */
    private function addRootSnippetCodeToPageSnippetCodesIfMissing(array $snippetCodes)
    {
        if (!in_array($this->rootSnippetCode, $snippetCodes)) {
            $snippetCodes[] = $this->rootSnippetCode;
        }
        return $snippetCodes;
    }
}
