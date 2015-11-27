<?php

namespace LizardsAndPumpkins\ContentDelivery;

use LizardsAndPumpkins\ContentDelivery\PageBuilder\PageBuilderSnippets;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DefaultHttpResponse;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\MissingSnippetCodeMessage;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\SnippetKeyGeneratorLocator;

class PageBuilder
{
    /**
     * @var string
     */
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

    /**
     * @var string[]
     */
    private $keyGeneratorParams;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array[]
     */
    private $snippetTransformations = [];

    /**
     * @var PageBuilderSnippets
     */
    private $pageSnippets;

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
     * @return DefaultHttpResponse
     */
    public function buildPage(PageMetaInfoSnippetContent $metaInfo, Context $context, array $keyGeneratorParams)
    {
        $this->context = $context;
        $this->keyGeneratorParams = $keyGeneratorParams;

        $codeToKeyMap = $this->initFromMetaInfo($metaInfo);
        $keyToContentMap = $this->loadSnippets();
        $this->pageSnippets = PageBuilderSnippets::fromKeyCodeAndContent($codeToKeyMap, $keyToContentMap);
        
        $this->logMissingSnippets();
        $this->applySnippetTransformations();

        $content = $this->pageSnippets->buildPageContent($this->rootSnippetCode);

        return DefaultHttpResponse::create($content, []);
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

    /**
     * @param PageMetaInfoSnippetContent $metaInfo
     * @return string[]
     */
    private function initFromMetaInfo(PageMetaInfoSnippetContent $metaInfo)
    {
        $this->rootSnippetCode = $metaInfo->getRootSnippetCode();
        $snippetCodes = $this->addRootSnippetCodeToPageSnippetCodesIfMissing($metaInfo->getPageSnippetCodes());
        $this->snippetCodeToKeyMap = array_merge($this->snippetCodeToKeyMap, array_combine(
            $snippetCodes,
            array_map([$this, 'tryToGetSnippetKey'], $snippetCodes)
        ));
        return $this->snippetCodeToKeyMap;
    }

    /**
     * @param string $snippetCode
     * @param callable $transformation
     */
    public function registerSnippetTransformation($snippetCode, callable $transformation)
    {
        if (!array_key_exists($snippetCode, $this->snippetTransformations)) {
            $this->snippetTransformations[$snippetCode] = [];
        }
        $this->snippetTransformations[$snippetCode][] = $transformation;
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

    /**
     * @return string[]
     */
    private function loadSnippets()
    {
        $keys = $this->getSnippetKeysInContext();
        $this->snippetKeyToContentMap = array_merge(
            $this->snippetKeyToContentMap,
            $this->dataPoolReader->getSnippets($keys)
        );
        return $this->snippetKeyToContentMap;
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
        $missingSnippetCodes = $this->pageSnippets->getNotLoadedSnippetCodes();
        if (count($missingSnippetCodes) > 0) {
            $this->logger->log(new MissingSnippetCodeMessage($missingSnippetCodes, $this->context));
        }
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

    private function applySnippetTransformations()
    {
        array_map(function ($snippetCode) {
            $this->applyTransformationToSnippetByCode($snippetCode);
        }, $this->getCodesOfSnippetsWithTransformations());
    }

    /**
     * @return string[]
     */
    private function getCodesOfSnippetsWithTransformations()
    {
        return array_intersect(
            array_keys($this->snippetTransformations),
            $this->pageSnippets->getSnippetCodes()
        );
    }

    /**
     * @param string $snippetCode
     */
    private function applyTransformationToSnippetByCode($snippetCode)
    {
        $this->pageSnippets->updateSnippetByCode(
            $snippetCode,
            array_reduce(
                $this->getTransformationsForSnippetByCode($snippetCode),
                [$this, 'applyTransformationToSnippetContent'],
                $this->pageSnippets->getSnippetByCode($snippetCode)
            )
        );
    }

    /**
     * @param string $content
     * @param callable $transformation
     * @return string
     */
    private function applyTransformationToSnippetContent($content, callable $transformation)
    {
        return $transformation($content, $this->context);
    }

    /**
     * @param string $snippetCode
     * @return callable[]
     */
    private function getTransformationsForSnippetByCode($snippetCode)
    {
        return $this->snippetTransformations[$snippetCode];
    }
}
