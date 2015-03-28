<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\KeyNotFoundException;

abstract class AbstractHttpRequestHandler implements HttpRequestHandler
{
    /**
     * @var UrlPathKeyGenerator
     */
    private $urlPathKeyGenerator;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var HttpUrl
     */
    private $httpUrl;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var string
     */
    private $rootSnippetCode;

    /**
     * @var string[]
     */
    private $snippetCodesToKeyMap;

    /**
     * @var string
     */
    private $pageSourceObjectId;

    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $keyGeneratorLocator;

    /**
     * @var string[]
     */
    private $snippets;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        HttpUrl $url,
        Context $context,
        UrlPathKeyGenerator $urlPathKeyGenerator,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        DataPoolReader $dataPoolReader,
        Logger $logger
    ) {
        $this->httpUrl = $url;
        $this->context = $context;
        $this->urlPathKeyGenerator = $urlPathKeyGenerator;
        $this->dataPoolReader = $dataPoolReader;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->logger = $logger;
    }

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
     * @return SnippetKeyGeneratorLocator
     */
    protected function getKeyGeneratorLocator()
    {
        return $this->keyGeneratorLocator;
    }

    /**
     * @return HttpUrl
     */
    protected function getHttpUrl()
    {
        return $this->httpUrl;
    }

    /**
     * @return Context
     */
    protected function getContext()
    {
        return $this->context;
    }

    /**
     * @return UrlPathKeyGenerator
     */
    protected function getUrlPathKeyGenerator()
    {
        return $this->urlPathKeyGenerator;
    }

    /**
     * @param string $key
     * @return string
     */
    abstract protected function getSnippetKeyInContext($key);

    /**
     * @param string $snippetJson
     * @return PageMetaInfoSnippetContent
     */
    abstract protected function createPageMetaInfoInstance($snippetJson);

    /**
     * @return string
     */
    abstract protected function getPageMetaInfoSnippetKey();

    private function loadPageMetaInfo()
    {
        if (is_null($this->rootSnippetCode)) {
            $pageUrlPathKey = $this->getPageMetaInfoSnippetKey();
            $snippetJson = $this->dataPoolReader->getSnippet($pageUrlPathKey);
            $metaInfo = $this->createPageMetaInfoInstance($snippetJson);
            $this->initPropertiesFromMetaInfo($metaInfo);
        }
    }

    private function initPropertiesFromMetaInfo(PageMetaInfoSnippetContent $metaInfo)
    {
        $this->pageSourceObjectId = $metaInfo->getSourceId();
        $this->rootSnippetCode = $metaInfo->getRootSnippetCode();

        $snippetCodes = $metaInfo->getPageSnippetCodes();
        $this->snippetCodesToKeyMap = array_combine(
            $snippetCodes,
            array_map([$this, 'getSnippetKeyInContext'], $snippetCodes)
        );
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
        $this->snippets = $this->dataPoolReader->getSnippets($keys);
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
        $generator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode($this->rootSnippetCode);
        return $generator->getKeyForContext($this->pageSourceObjectId, $this->context);
    }

    /**
     * @param string $snippetKey
     * @return string
     * @throws InvalidPageMetaSnippetException
     */
    private function getSnippetByKey($snippetKey)
    {
        if (!array_key_exists($snippetKey, $this->snippets)) {
            throw new InvalidPageMetaSnippetException(sprintf(
                'Snippet not available (key "%s", source id "%s", context "%s")',
                $snippetKey,
                $this->pageSourceObjectId,
                $this->context->getId()
            ));
        }
        return $this->snippets[$snippetKey];
    }

    private function logMissingSnippetCodes()
    {
        $missingSnippetCodes = $this->getMissingSnippetCodes();
        if (count($missingSnippetCodes) > 0) {
            $this->logger->log(new MissingSnippetCodeMessage($missingSnippetCodes));
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
