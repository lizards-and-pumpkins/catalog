<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpRequestHandler;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\KeyNotFoundException;

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
    private $snippets;

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
        $this->logMissingSnippetCodes();

        list($rootSnippet, $childSnippets) = $this->separateRootAndChildSnippets();

        $childSnippetsCodes = $this->getLoadedChildSnippetCodes();
        $childSnippetCodesToContentMap = $this->mergePlaceholderAndSnippets($childSnippetsCodes, $childSnippets);

        $content = $this->injectSnippetsIntoContent($rootSnippet, $childSnippetCodesToContentMap);

        return new Page($content);
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
        $this->snippets = $this->getDataPoolReader()->getSnippets($keys);
    }

    /**
     * @return string[]
     */
    private function separateRootAndChildSnippets()
    {
        $rootSnippetKey = $this->getRootSnippetKey();
        $rootSnippet = $this->getSnippetByKey($rootSnippetKey);
        $childSnippets = array_diff_key($this->snippets, [$rootSnippetKey => $rootSnippet]);
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
                   array_key_exists($this->snippetCodesToKeyMap[$code], $this->snippets);
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
        if (!array_key_exists($snippetKey, $this->snippets)) {
            throw new InvalidPageMetaSnippetException($this->formatSnippetNotAvailableErrorMessage($snippetKey));
        }
        return $this->snippets[$snippetKey];
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
            if (!array_key_exists($key, $this->snippets)) {
                $missingSnippetCodes[] = $code;
            }
        }
        return $missingSnippetCodes;
    }
}
