<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\PageMetaInfoSnippetContent;

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
     * @param string $productId
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     */
    private function __construct($productId, $rootSnippetCode, array $pageSnippetCodes)
    {
        $this->productId = $productId;
        $this->rootSnippetCode = $rootSnippetCode;
        $this->pageSnippetCodes = $pageSnippetCodes;
    }

    /**
     * @param string $productId
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @return ProductDetailPageMetaInfoSnippetContent
     */
    public static function create($productId, $rootSnippetCode, array $pageSnippetCodes)
    {
        self::validateProductId($productId);
        self::validateRootSnippetCode($rootSnippetCode);
        $pageSnippetCodes = array_unique(array_merge(
            [
                $rootSnippetCode,
                ProductJsonSnippetRenderer::CODE,
                PriceSnippetRenderer::PRICE,
                PriceSnippetRenderer::SPECIAL_PRICE,
            ],
            $pageSnippetCodes
        ));
        return new self($productId, $rootSnippetCode, $pageSnippetCodes);
    }

    /**
     * @param string $json
     * @return ProductDetailPageMetaInfoSnippetContent
     */
    public static function fromJson($json)
    {
        $pageInfo = self::decodeJson($json);
        self::validateRequiredKeysArePresent($pageInfo);
        return static::create(
            $pageInfo[self::KEY_PRODUCT_ID],
            $pageInfo[self::KEY_ROOT_SNIPPET_CODE],
            $pageInfo[self::KEY_PAGE_SNIPPET_CODES]
        );
    }

    /**
     * @param mixed $sourceId
     * @return string
     */
    private static function getNonScalarTypeRepresentation($sourceId)
    {
        return is_object($sourceId) ?
            get_class($sourceId) :
            gettype($sourceId);
    }

    /**
     * @param mixed[] $pageInfo
     */
    protected static function validateRequiredKeysArePresent(array $pageInfo)
    {
        foreach ([self::KEY_PRODUCT_ID, self::KEY_ROOT_SNIPPET_CODE, self::KEY_PAGE_SNIPPET_CODES] as $key) {
            if (!array_key_exists($key, $pageInfo)) {
                throw new \RuntimeException(sprintf('Missing key in input JSON: "%s"', $key));
            }
        }
    }

    /**
     * @param string $json
     * @return mixed[]
     */
    private static function decodeJson($json)
    {
        $result = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \OutOfBoundsException(sprintf(
                'JSON decode error: %s',
                json_last_error_msg()
            ));
        }
        return $result;
    }

    /**
     * @return mixed[]
     */
    public function getInfo()
    {
        return [
            self::KEY_PRODUCT_ID         => $this->productId,
            self::KEY_ROOT_SNIPPET_CODE  => $this->rootSnippetCode,
            self::KEY_PAGE_SNIPPET_CODES => $this->pageSnippetCodes,
        ];
    }

    /**
     * @param mixed $sourceId
     */
    private static function validateProductId($sourceId)
    {
        if (!is_scalar($sourceId)) {
            throw new \InvalidArgumentException(sprintf(
                'The page meta info source id has to be a scalar value, got "%s"',
                self::getNonScalarTypeRepresentation($sourceId)
            ));
        }
    }

    /**
     * @param mixed $rootSnippetCode
     */
    private static function validateRootSnippetCode($rootSnippetCode)
    {
        if (!is_string($rootSnippetCode)) {
            throw new \InvalidArgumentException(sprintf(
                'The page meta info root snippet code has to be a string value, got "%s"',
                gettype($rootSnippetCode)
            ));
        }
    }

    /**
     * @return string
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @return string
     */
    public function getRootSnippetCode()
    {
        return $this->rootSnippetCode;
    }

    /**
     * @return string[]
     */
    public function getPageSnippetCodes()
    {
        return $this->pageSnippetCodes;
    }
}
