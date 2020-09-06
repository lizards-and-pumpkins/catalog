<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\Import\SnippetContainer;
use LizardsAndPumpkins\Util\SnippetCodeValidator;

class ProductDetailPageMetaInfoSnippetContent implements PageMetaInfoSnippetContent
{
    const KEY_PRODUCT_ID = 'product_id';

    /**
     * @var string
     */
    private $productId;

    /**
     * @var string
     */
    private $rootSnippetCode;

    /**
     * @var string[]
     */
    private $pageSnippetCodes;

    /**
     * @var SnippetContainer[]
     */
    private $containers;

    /**
     * @var array[]
     */
    private $pageSpecificData;

    /**
     * @param string $productId
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @param SnippetContainer[] $containers
     * @param array[] $pageSpecificData
     */
    private function __construct(
        string $productId,
        string $rootSnippetCode,
        array $pageSnippetCodes,
        array $containers,
        array $pageSpecificData
    ) {
        $this->productId = $productId;
        $this->rootSnippetCode = $rootSnippetCode;
        $this->pageSnippetCodes = $pageSnippetCodes;
        $this->containers = $containers;
        $this->pageSpecificData = $pageSpecificData;
    }

    /**
     * @param string $productId
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @param array[] $containerData
     * @param array[] $pageSpecificData
     * @return ProductDetailPageMetaInfoSnippetContent
     */
    public static function create(
        string $productId,
        string $rootSnippetCode,
        array $pageSnippetCodes,
        array $containerData,
        array $pageSpecificData
    ): ProductDetailPageMetaInfoSnippetContent {
        SnippetCodeValidator::validate($rootSnippetCode);
        $pageSnippetCodes = array_unique(array_merge(
            [
                $rootSnippetCode,
                ProductJsonSnippetRenderer::CODE,
                PriceSnippetRenderer::PRICE,
                PriceSnippetRenderer::SPECIAL_PRICE,
            ],
            $pageSnippetCodes
        ));

        $snippetContainers = self::createSnippetContainers($containerData);

        return new self($productId, $rootSnippetCode, $pageSnippetCodes, $snippetContainers, $pageSpecificData);
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
     * @return ProductDetailPageMetaInfoSnippetContent
     */
    public static function fromArray(array $pageMeta): ProductDetailPageMetaInfoSnippetContent
    {
        self::validateRequiredKeysArePresent($pageMeta);

        return static::create(
            $pageMeta[self::KEY_PRODUCT_ID],
            $pageMeta[self::KEY_ROOT_SNIPPET_CODE],
            $pageMeta[self::KEY_PAGE_SNIPPET_CODES],
            $pageMeta[self::KEY_CONTAINER_SNIPPETS],
            $pageMeta[self::KEY_PAGE_SPECIFIC_DATA]
        );
    }

    /**
     * @param mixed[] $pageInfo
     */
    private static function validateRequiredKeysArePresent(array $pageInfo): void
    {
        $requiredKeys = [
            self::KEY_PRODUCT_ID,
            self::KEY_ROOT_SNIPPET_CODE,
            self::KEY_PAGE_SNIPPET_CODES,
            self::KEY_CONTAINER_SNIPPETS,
            self::KEY_PAGE_SPECIFIC_DATA,
        ];

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $pageInfo)) {
                throw new \RuntimeException(sprintf('Missing key in input array: "%s"', $key));
            }
        }
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            self::KEY_HANDLER_CODE => ProductDetailViewRequestHandler::CODE,
            self::KEY_PRODUCT_ID => $this->productId,
            self::KEY_ROOT_SNIPPET_CODE => $this->rootSnippetCode,
            self::KEY_PAGE_SNIPPET_CODES => $this->pageSnippetCodes,
            self::KEY_CONTAINER_SNIPPETS => $this->getContainerSnippets(),
            self::KEY_PAGE_SPECIFIC_DATA => $this->pageSpecificData,
        ];
    }

    public function getProductId(): string
    {
        return $this->productId;
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
        return array_reduce($this->containers, function ($carry, SnippetContainer $container) {
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
}
