<?php

namespace Brera\Http;

use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\KeyNotFoundException;
use Brera\InvalidPageMetaSnippetException;
use Brera\Logger;
use Brera\MissingSnippetCodeMessage;
use Brera\Page;
use Brera\PageMetaInfoSnippetContent;

abstract class AbstractHttpRequestHandler implements HttpRequestHandler
{

    /**
     * @var string
     */
    private $rootSnippetCode;

    /**
     * @var string[]
     */
    private $snippetCodesToKeyMap;

    /**
     * @var string[]
     */
    private $snippetKeyToContentMap;

    /**
     * @return bool
     */
    public function canProcess()
    {
        try {
            $this->getRootSnippetCode();
            return true;
        } catch (KeyNotFoundException $e) {
            return false;
        }
    }

    /**
     * @return Page
     */
    public function process()
    {
        $this->loadPageMetaInfo();
        $this->loadSnippets();
        $this->mergePageSpecificAdditionalSnippetsHook();
        $this->logMissingSnippetCodes();

        list($rootSnippet, $childSnippets) = $this->separateRootAndChildSnippets();

        $childSnippetsCodes = $this->getLoadedChildSnippetCodes();
        $childSnippetCodesToContentMap = $this->mergePlaceholderAndSnippets($childSnippetsCodes, $childSnippets);
        
        $content = $this->injectSnippetsIntoContent($rootSnippet, $childSnippetCodesToContentMap);

        return new Page($content);
    }

    protected function mergePageSpecificAdditionalSnippetsHook()
    {
        // Intentionally left empty as a hook method for concrete implementations
    }

    /**
     * @param string[] $snippetKeyToContentMap
     */
    final protected function mergeSnippetKeyToContentMap(array $snippetKeyToContentMap)
    {
        $this->snippetKeyToContentMap = array_merge($this->snippetKeyToContentMap, $snippetKeyToContentMap);
    }

    /**
     * @param string[] $snippetCodeToKeyMap
     */
    final protected function mergeSnippetCodeToKeyMap(array $snippetCodeToKeyMap)
    {
        $this->snippetCodesToKeyMap = array_merge($this->snippetCodesToKeyMap, $snippetCodeToKeyMap);
    }

    /**
     * @return string
     */
    abstract protected function getPageMetaInfoSnippetKey();

    /**
     * @param string $snippetJson
     * @return PageMetaInfoSnippetContent
     */
    abstract protected function createPageMetaInfoInstance($snippetJson);

    /**
     * @param string $snippetCode
     * @return string
     */
    abstract protected function getSnippetKey($snippetCode);

    /**
     * @param string $snippetKey
     * @return string string
     */
    abstract protected function formatSnippetNotAvailableErrorMessage($snippetKey);

    /**
     * @return DataPoolReader
     */
    abstract protected function getDataPoolReader();

    /**
     * @return Logger
     */
    abstract protected function getLogger();

    private function loadPageMetaInfo()
    {
        if (is_null($this->rootSnippetCode)) {
            $pageUrlPathKey = $this->getPageMetaInfoSnippetKey();
            $snippetJson = $this->getDataPoolReader()->getSnippet($pageUrlPathKey);
            $metaInfo = $this->createPageMetaInfoInstance($snippetJson);
            $this->initPropertiesFromMetaInfo($metaInfo);
        }
    }

    private function initPropertiesFromMetaInfo(PageMetaInfoSnippetContent $metaInfo)
    {
        $this->rootSnippetCode = $metaInfo->getRootSnippetCode();

        $snippetCodes = $metaInfo->getPageSnippetCodes();
        $this->snippetCodesToKeyMap = array_combine(
            $snippetCodes,
            array_map([$this, 'getSnippetKeyDefaultingToEmpty'], $snippetCodes)
        );
    }

    /**
     * @param string $snippetCode
     * @return string
     */
    private function getSnippetKeyDefaultingToEmpty($snippetCode)
    {
        try {
            return $this->getSnippetKey($snippetCode);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @return string[]
     */
    private function getSnippetKeysInContext()
    {
        return array_values($this->snippetCodesToKeyMap);
    }

    private function loadSnippets()
    {
        $keys = $this->getSnippetKeysInContext();
        $this->snippetKeyToContentMap = $this->getDataPoolReader()->getSnippets($keys);
    }

    /**
     * @return string[]
     */
    private function separateRootAndChildSnippets()
    {
        $rootSnippetKey = $this->getRootSnippetKey();
        $rootSnippet = $this->getSnippetByKey($rootSnippetKey);
        $childSnippets = array_diff_key($this->snippetKeyToContentMap, [$rootSnippetKey => $rootSnippet]);
        return [$rootSnippet, $childSnippets];
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
     * @todo: delegate placeholder creation (and also use the delegate during import)
     * @see Brera\Renderer\BlockRenderer::getBlockPlaceholder()
     */
    private function buildPlaceholderFromCode($code)
    {
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
     * @return string
     */
    private function getRootSnippetCode()
    {
        $this->loadPageMetaInfo();
        return $this->rootSnippetCode;
    }

    /**
     * @return string[]
     */
    private function getLoadedChildSnippetCodes()
    {
        return array_filter(array_keys($this->snippetCodesToKeyMap), function ($code) {
            return $code !== $this->rootSnippetCode &&
                   array_key_exists($this->snippetCodesToKeyMap[$code], $this->snippetKeyToContentMap);
        });
    }

    /**
     * @return string
     */
    private function getRootSnippetKey()
    {
        return $this->getSnippetKeyDefaultingToEmpty($this->rootSnippetCode);
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

    private function logMissingSnippetCodes()
    {
        $missingSnippetCodes = $this->getMissingSnippetCodes();
        if (count($missingSnippetCodes) > 0) {
            $this->getLogger()->log(new MissingSnippetCodeMessage($missingSnippetCodes));
        }
    }

    /**
     * @return string[]
     */
    private function getMissingSnippetCodes()
    {
        $missingSnippetCodes = [];
        foreach ($this->snippetCodesToKeyMap as $code => $key) {
            if (!array_key_exists($key, $this->snippetKeyToContentMap)) {
                $missingSnippetCodes[] = $code;
            }
        }
        return $missingSnippetCodes;
    }
}
