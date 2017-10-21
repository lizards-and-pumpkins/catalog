<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\SnippetContainer;
use LizardsAndPumpkins\Util\SnippetCodeValidator;

class ProductSearchResultMetaSnippetContent implements PageMetaInfoSnippetContent
{
    /**
     * @var string
     */
    private $rootSnippetCode;

    /**
     * @var string[]
     */
    private $pageSnippetCodes;

    /**
     * @var array|SnippetContainer[]
     */
    private $containerSnippets;

    /**
     * @var array[]
     */
    private $pageSpecificData;

    /**
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @param SnippetContainer[] $containerSnippets
     * @param array[] $pageSpecificData
     */
    private function __construct(
        string $rootSnippetCode,
        array $pageSnippetCodes,
        array $containerSnippets,
        array $pageSpecificData
    ) {
        $this->rootSnippetCode = $rootSnippetCode;
        $this->pageSnippetCodes = $pageSnippetCodes;
        $this->containerSnippets = $containerSnippets;
        $this->pageSpecificData = $pageSpecificData;
    }

    /**
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @param array[] $containerData
     * @param array[] $pageSpecificData
     * @return ProductSearchResultMetaSnippetContent
     */
    public static function create(
        string $rootSnippetCode,
        array $pageSnippetCodes,
        array $containerData,
        array $pageSpecificData
    ): ProductSearchResultMetaSnippetContent {
        SnippetCodeValidator::validate($rootSnippetCode);

        if (! in_array($rootSnippetCode, $pageSnippetCodes)) {
            $pageSnippetCodes = array_merge([$rootSnippetCode], $pageSnippetCodes);
        }

        $snippetContainers = self::createSnippetContainers($containerData);

        return new self($rootSnippetCode, $pageSnippetCodes, $snippetContainers, $pageSpecificData);
    }

    /**
     * @param array[] $containerArray
     * @return SnippetContainer[]
     */
    private static function createSnippetContainers(array $containerArray): array
    {
        return array_map(function ($code) use ($containerArray) {
            return new SnippetContainer($code, $containerArray[$code]);
        }, array_keys($containerArray));
    }

    /**
     * @param mixed[] $pageMeta
     * @return ProductSearchResultMetaSnippetContent
     */
    public static function fromArray(array $pageMeta): ProductSearchResultMetaSnippetContent
    {
        self::validateRequiredKeysArePresent($pageMeta);

        return self::create(
            $pageMeta[self::KEY_ROOT_SNIPPET_CODE],
            $pageMeta[self::KEY_PAGE_SNIPPET_CODES],
            $pageMeta[self::KEY_CONTAINER_SNIPPETS],
            $pageMeta[self::KEY_PAGE_SPECIFIC_DATA]
        );
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            self::KEY_HANDLER_CODE => ProductSearchRequestHandler::CODE,
            self::KEY_ROOT_SNIPPET_CODE => $this->rootSnippetCode,
            self::KEY_PAGE_SNIPPET_CODES => $this->pageSnippetCodes,
            self::KEY_CONTAINER_SNIPPETS => $this->getContainerSnippets(),
            self::KEY_PAGE_SPECIFIC_DATA => $this->pageSpecificData,
        ];
    }

    public function getRootSnippetCode(): string
    {
        return $this->rootSnippetCode;
    }

    /**
     * @return string[]
     */
    public function getPageSnippetCodes(): array
    {
        return $this->pageSnippetCodes;
    }

    /**
     * @return array[]
     */
    public function getContainerSnippets(): array
    {
        return array_reduce($this->containerSnippets, function ($carry, SnippetContainer $container) {
            return array_merge($carry, $container->toArray());
        }, []);
    }

    /**
     * @return array[]
     */
    public function getPageSpecificData(): array
    {
        return $this->pageSpecificData;
    }

    /**
     * @param mixed[] $pageMetaInfo
     */
    private static function validateRequiredKeysArePresent(array $pageMetaInfo)
    {
        $requiredKeys = [
            self::KEY_ROOT_SNIPPET_CODE,
            self::KEY_PAGE_SNIPPET_CODES,
            self::KEY_CONTAINER_SNIPPETS,
            self::KEY_PAGE_SPECIFIC_DATA,
        ];

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $pageMetaInfo)) {
                throw new \RuntimeException(sprintf('Missing "%s" in input array', $key));
            }
        }
    }
}
