<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\Import\SnippetContainer;
use LizardsAndPumpkins\Import\SnippetCode;

class ProductDetailPageMetaInfoSnippetContent implements PageMetaInfoSnippetContent
{
    const KEY_PRODUCT_ID = 'product_id';

    private static $requiredKeys = [
        self::KEY_PRODUCT_ID,
        self::KEY_ROOT_SNIPPET_CODE,
        self::KEY_PAGE_SNIPPET_CODES,
        self::KEY_CONTAINER_SNIPPETS,
    ];

    /**
     * @var string
     */
    private $productId;

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
    private $containers;

    /**
     * @param string $productId
     * @param SnippetCode $rootSnippetCode
     * @param SnippetCode[] $pageSnippetCodes
     * @param SnippetContainer[] $containers
     */
    private function __construct(
        string $productId,
        SnippetCode $rootSnippetCode,
        array $pageSnippetCodes,
        array $containers
    ) {
        $this->productId = $productId;
        $this->rootSnippetCode = $rootSnippetCode;
        $this->pageSnippetCodes = $pageSnippetCodes;
        $this->containers = $containers;
    }

    public static function create(
        string $productId,
        SnippetCode $rootSnippetCode,
        array $pageSnippetCodes,
        array $containerData
    ): ProductDetailPageMetaInfoSnippetContent {
        $pageSnippetCodes = array_unique(array_merge(
            [
                $rootSnippetCode,
                new SnippetCode(ProductJsonSnippetRenderer::CODE),
                new SnippetCode(PriceSnippetRenderer::PRICE),
                new SnippetCode(PriceSnippetRenderer::SPECIAL_PRICE),
            ],
            $pageSnippetCodes
        ));

        return new self($productId, $rootSnippetCode, $pageSnippetCodes, self::createSnippetContainers($containerData));
    }

    /**
     * @param SnippetCode[] $containerArray
     * @return SnippetContainer[]
     */
    private static function createSnippetContainers(array $containerArray): array
    {
        return array_map(function (string $snippetCodeString) use ($containerArray) {
            return new SnippetContainer(new SnippetCode($snippetCodeString), ...$containerArray[$snippetCodeString]);
        }, array_keys($containerArray));
    }

    public static function fromJson(string $json): ProductDetailPageMetaInfoSnippetContent
    {
        $pageInfo = self::decodeJson($json);
        self::validateRequiredKeysArePresent($pageInfo);

        $rootSnippetCode = new SnippetCode($pageInfo[self::KEY_ROOT_SNIPPET_CODE]);

        $pageSnippetCodes = array_map(function (string $snippetCodeString) {
            return new SnippetCode($snippetCodeString);
        }, $pageInfo[self::KEY_PAGE_SNIPPET_CODES]);

        return static::create(
            $pageInfo[self::KEY_PRODUCT_ID],
            $rootSnippetCode,
            $pageSnippetCodes,
            $pageInfo[self::KEY_CONTAINER_SNIPPETS]
        );
    }

    /**
     * @param mixed[] $pageInfo
     */
    private static function validateRequiredKeysArePresent(array $pageInfo)
    {
        foreach (self::$requiredKeys as $key) {
            if (! array_key_exists($key, $pageInfo)) {
                throw new \RuntimeException(sprintf('Missing key in input JSON: "%s"', $key));
            }
        }
    }

    /**
     * @param string $json
     * @return mixed[]
     */
    private static function decodeJson(string $json): array
    {
        $result = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \OutOfBoundsException(sprintf('JSON decode error: %s', json_last_error_msg()));
        }

        return $result;
    }

    /**
     * @return mixed[]
     */
    public function getInfo(): array
    {
        return [
            self::KEY_PRODUCT_ID => $this->productId,
            self::KEY_ROOT_SNIPPET_CODE => (string) $this->rootSnippetCode,
            self::KEY_PAGE_SNIPPET_CODES => $this->pageSnippetCodes,
            self::KEY_CONTAINER_SNIPPETS => $this->getContainerSnippets(),
        ];
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId;
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
        return array_reduce($this->containers, function ($carry, SnippetContainer $container) {
            return array_merge($carry, $container->toArray());
        }, []);
    }
}
