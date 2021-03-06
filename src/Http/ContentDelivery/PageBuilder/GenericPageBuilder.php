<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Http\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;

class GenericPageBuilder implements PageBuilder
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
     * @return HttpResponse
     */
    public function buildPage(
        PageMetaInfoSnippetContent $metaInfo,
        Context $context,
        array $keyGeneratorParams
    ) : HttpResponse {
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

        return GenericHttpResponse::create($body, $headers, HttpResponse::STATUS_OK);
    }

    /**
     * @param string[] $snippetCodeToKeyMap
     * @param string[] $snippetKeyToContentMap
     */
    public function addSnippetsToPage(array $snippetCodeToKeyMap, array $snippetKeyToContentMap): void
    {
        $this->snippetCodeToKeyMap = array_merge($this->snippetCodeToKeyMap, $snippetCodeToKeyMap);
        $this->snippetKeyToContentMap = array_merge($this->snippetKeyToContentMap, $snippetKeyToContentMap);
    }

    /**
     * @param PageMetaInfoSnippetContent $metaInfo
     * @return string[]
     */
    private function initFromMetaInfo(PageMetaInfoSnippetContent $metaInfo) : array
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
    private function getFlattenedContainerSnippetCodes() : array
    {
        return array_reduce($this->containerSnippets, function (array $flattened, array $snippetsInContainer) {
            return array_merge($flattened, $snippetsInContainer);
        }, []);
    }

    public function registerSnippetTransformation(string $snippetCode, callable $transformation): void
    {
        if (!array_key_exists($snippetCode, $this->snippetTransformations)) {
            $this->snippetTransformations[$snippetCode] = [];
        }
        $this->snippetTransformations[$snippetCode][] = $transformation;
    }

    public function addSnippetToContainer(string $containerCode, string $snippetCode): void
    {
        $this->containerSnippets[$containerCode][] = $snippetCode;
    }

    public function addSnippetToPage(string $snippetCode, string $snippetContent): void
    {
        $this->addSnippetsToPage([$snippetCode => $snippetCode], [$snippetCode => $snippetContent]);
    }

    private function tryToGetSnippetKey(string $snippetCode) : string
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
    private function loadSnippets() : array
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
    private function getSnippetKeysInContext() : array
    {
        return array_values($this->removeCodesThatCouldNotBeMappedToAKey($this->snippetCodeToKeyMap));
    }

    /**
     * @param string[] $snippetKeys
     * @return string[]
     */
    private function removeCodesThatCouldNotBeMappedToAKey(array $snippetKeys) : array
    {
        return array_filter($snippetKeys);
    }

    private function applySnippetTransformations(): void
    {
        every($this->getCodesOfSnippetsWithTransformations(), function ($snippetCode) {
            $this->applyTransformationToSnippetByCode($snippetCode);
        });
    }

    /**
     * @return string[]
     */
    private function getCodesOfSnippetsWithTransformations() : array
    {
        return array_intersect(
            array_keys($this->snippetTransformations),
            $this->pageSnippets->getSnippetCodes()
        );
    }

    private function applyTransformationToSnippetByCode(string $snippetCode): void
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

    private function applyTransformationToSnippetContent(string $content, callable $transformation) : string
    {
        return $transformation($content, $this->context, $this->pageSnippets);
    }

    /**
     * @param string $snippetCode
     * @return callable[]
     */
    private function getTransformationsForSnippetByCode(string $snippetCode) : array
    {
        return $this->snippetTransformations[$snippetCode];
    }
}
