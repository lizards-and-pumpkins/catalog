<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\SnippetContainer;
use LizardsAndPumpkins\Import\SnippetCode;

class ProductSearchResultMetaSnippetContent implements PageMetaInfoSnippetContent
{
    /**
     * @var SnippetCode
     */
    private $rootSnippetCode;

    /**
     * @var SnippetCode[]
     */
    private $pageSnippetCodes;

    /**
     * @var SnippetContainer[]
     */
    private $containerSnippets;

    /**
     * @param SnippetCode $rootSnippetCode
     * @param SnippetCode[] $pageSnippetCodes
     * @param SnippetContainer[] $containerSnippets
     */
    private function __construct(SnippetCode $rootSnippetCode, array $pageSnippetCodes, array $containerSnippets)
    {
        $this->rootSnippetCode = $rootSnippetCode;
        $this->pageSnippetCodes = $pageSnippetCodes;
        $this->containerSnippets = $containerSnippets;
    }

    /**
     * @param SnippetCode $rootSnippetCode
     * @param SnippetCode[] $pageSnippetCodes
     * @param array[] $containerData
     * @return ProductSearchResultMetaSnippetContent
     */
    public static function create(
        SnippetCode $rootSnippetCode,
        array $pageSnippetCodes,
        array $containerData
    ): ProductSearchResultMetaSnippetContent {
        if (! in_array($rootSnippetCode, $pageSnippetCodes)) {
            $pageSnippetCodes = array_merge([$rootSnippetCode], $pageSnippetCodes);
        }

        return new self($rootSnippetCode, $pageSnippetCodes, self::createSnippetContainers($containerData));
    }

    /**
     * @param array[] $containerArray
     * @return SnippetContainer[]
     */
    private static function createSnippetContainers(array $containerArray): array
    {
        return array_map(function (string $snippetCodeString) use ($containerArray) {
            return new SnippetContainer(new SnippetCode($snippetCodeString), ...$containerArray[$snippetCodeString]);
        }, array_keys($containerArray));
    }

    public static function fromJson(string $json): ProductSearchResultMetaSnippetContent
    {
        $pageMetaInfo = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \OutOfBoundsException(sprintf('JSON decode error: %s', json_last_error_msg()));
        }

        foreach ([self::KEY_ROOT_SNIPPET_CODE, self::KEY_PAGE_SNIPPET_CODES, self::KEY_CONTAINER_SNIPPETS] as $key) {
            if (! array_key_exists($key, $pageMetaInfo)) {
                throw new \RuntimeException(sprintf('Missing "%s" in input JSON', $key));
            }
        }

        $rootSnippetCode = new SnippetCode($pageMetaInfo[self::KEY_ROOT_SNIPPET_CODE]);

        $pageSnippetCodes = array_map(function (string $snippetCodeString) {
            return new SnippetCode($snippetCodeString);
        }, $pageMetaInfo[self::KEY_PAGE_SNIPPET_CODES]);

        return self::create($rootSnippetCode, $pageSnippetCodes, $pageMetaInfo[self::KEY_CONTAINER_SNIPPETS]);
    }

    /**
     * @return mixed[]
     */
    public function getInfo(): array
    {
        return [
            self::KEY_ROOT_SNIPPET_CODE => $this->rootSnippetCode,
            self::KEY_PAGE_SNIPPET_CODES => $this->pageSnippetCodes,
            self::KEY_CONTAINER_SNIPPETS => $this->getContainerSnippets(),
        ];
    }

    public function getRootSnippetCode(): SnippetCode
    {
        return $this->rootSnippetCode;
    }

    /**
     * @return SnippetCode[]
     */
    public function getPageSnippetCodes(): array
    {
        return $this->pageSnippetCodes;
    }

    /**
     * @return SnippetCode[]
     */
    public function getContainerSnippets(): array
    {
        return array_reduce($this->containerSnippets, function ($carry, SnippetContainer $container) {
            return array_merge($carry, $container->toArray());
        }, []);
    }
}
