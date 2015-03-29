<?php

namespace Brera\Product;

use Brera\PageMetaInfoSnippetContent;

class ProductListingMetaInfoSnippetContent implements PageMetaInfoSnippetContent
{
    const KEY_CRITERIA = 'product_selection_criteria';

    /**
     * @var string
     */
    private $selectionCriteria;

    /**
     * @var string
     */
    private $rootSnippetCode;

    /**
     * @var string[]
     */
    private $pageSnippetCodes;

    /**
     * @param array $productSelectionCriteria
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     */
    private function __construct(array $productSelectionCriteria, $rootSnippetCode, array $pageSnippetCodes)
    {
        $this->selectionCriteria = $productSelectionCriteria;
        $this->rootSnippetCode = $rootSnippetCode;
        $this->pageSnippetCodes = $pageSnippetCodes;
    }

    /**
     * @param string[] $selectionCriteria
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @return ProductListingMetaInfoSnippetContent
     */
    public static function create(array $selectionCriteria, $rootSnippetCode, array $pageSnippetCodes)
    {
        self::validateRootSnippetCode($rootSnippetCode);
        if (!in_array($rootSnippetCode, $pageSnippetCodes)) {
            $pageSnippetCodes = array_merge([$rootSnippetCode], $pageSnippetCodes);
        }
        return new self($selectionCriteria, $rootSnippetCode, $pageSnippetCodes);
    }

    /**
     * @param string $json
     * @return ProductListingMetaInfoSnippetContent
     */
    public static function fromJson($json)
    {
        $pageInfo = self::decodeJson($json);
        self::validateRequiredKeysArePresent($pageInfo);
        return static::create(
            $pageInfo[self::KEY_CRITERIA],
            $pageInfo[self::KEY_ROOT_SNIPPET_CODE],
            $pageInfo[self::KEY_PAGE_SNIPPET_CODES]
        );
    }

    /**
     * @param mixed[] $pageInfo
     */
    protected static function validateRequiredKeysArePresent(array $pageInfo)
    {
        foreach ([self::KEY_CRITERIA, self::KEY_ROOT_SNIPPET_CODE, self::KEY_PAGE_SNIPPET_CODES] as $key) {
            if (!array_key_exists($key, $pageInfo)) {
                throw new \RuntimeException(sprintf('Missing key in input JSON: "%s"', $key));
            }
        }
    }

    /**
     * @param string $json
     * @return mixed[]
     * @throws \OutOfBoundsException
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
            self::KEY_CRITERIA => $this->selectionCriteria,
            self::KEY_ROOT_SNIPPET_CODE => $this->rootSnippetCode,
            self::KEY_PAGE_SNIPPET_CODES => $this->pageSnippetCodes
        ];
    }

    /**
     * @param string $rootSnippetCode
     */
    private static function validateRootSnippetCode($rootSnippetCode)
    {
        if (! is_string($rootSnippetCode)) {
            throw new \InvalidArgumentException(sprintf(
                'The page meta info root snippet code has to be a string value, got "%s"',
                gettype($rootSnippetCode)
            ));
        }
    }

    /**
     * @return string[]
     */
    public function getSelectionCriteria()
    {
        return $this->selectionCriteria;
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
