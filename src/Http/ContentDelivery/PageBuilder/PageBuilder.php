<?php

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;

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
     * @var array[]
     */
    private $snippetTransformations = [];

    /**
     * @var PageBuilderSnippets
     */
    private $pageSnippets;

    /**
     * @var array[]
     */
    private $containerSnippets = [];

    public function __construct(DataPoolReader $dataPoolReader, SnippetKeyGeneratorLocator $keyGeneratorLocator)
    {
        $this->dataPoolReader = $dataPoolReader;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
    }

    /**
     * @param PageMetaInfoSnippetContent $metaInfo
     * @param Context $context
     * @param mixed[] $keyGeneratorParams
     * @return GenericHttpResponse
     */
    public function buildPage(PageMetaInfoSnippetContent $metaInfo, Context $context, array $keyGeneratorParams)
    {
        $this->context = $context;
        $this->keyGeneratorParams = $keyGeneratorParams;

        $codeToKeyMap = $this->initFromMetaInfo($metaInfo);
        $keyToContentMap = $this->loadSnippets();
        $this->pageSnippets = PageBuilderSnippets::fromCodesAndContent(
            $codeToKeyMap,
            $keyToContentMap,
            array_merge_recursive($metaInfo->getContainerSnippets(), $this->containerSnippets)
        );

        $this->applySnippetTransformations();

        $body = $this->pageSnippets->buildPageContent($this->rootSnippetCode);
        $headers = [];
        $statusCode = 200;

        return GenericHttpResponse::create($body, $headers, $statusCode);
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
        $containerSnippetCodes = array_reduce($metaInfo->getContainerSnippets(), function ($carry, $codes) {
            return array_merge($carry, $codes);
        }, $this->getFlattenedContainerSnippetCodes());
        $snippetCodes = array_unique(array_merge(
            $metaInfo->getPageSnippetCodes(),
            [$this->rootSnippetCode],
            $containerSnippetCodes
        ));
        $this->snippetCodeToKeyMap = array_merge($this->snippetCodeToKeyMap, array_combine(
            $snippetCodes,
            array_map([$this, 'tryToGetSnippetKey'], $snippetCodes)
        ));
        return $this->snippetCodeToKeyMap;
    }

    /**
     * @return string[]
     */
    private function getFlattenedContainerSnippetCodes()
    {
        return array_reduce($this->containerSnippets, function (array $flattened, array $snippetsInContainer) {
            return array_merge($flattened, $snippetsInContainer);
        }, []);
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
     * @param string $containerCode
     * @param string $snippetCode
     */
    public function addSnippetToContainer($containerCode, $snippetCode)
    {
        $this->containerSnippets[$containerCode][] = $snippetCode;
    }

    /**
     * @param string $snippetCode
     * @param string $snippetContent
     */
    public function addSnippetToPage($snippetCode, $snippetContent)
    {
        $this->addSnippetsToPage([$snippetCode => $snippetCode], [$snippetCode => $snippetContent]);
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
        return $transformation($content, $this->context, $this->pageSnippets);
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
