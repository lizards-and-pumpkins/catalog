<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\SnippetContainer;
use LizardsAndPumpkins\ProductListing\Import\Exception\MalformedSearchCriteriaMetaException;
use LizardsAndPumpkins\Util\SnippetCodeValidator;

class ProductListingSnippetContent implements PageMetaInfoSnippetContent
{
    const KEY_CRITERIA = 'product_selection_criteria';

    private static $requiredKeys = [
        self::KEY_CRITERIA,
        self::KEY_ROOT_SNIPPET_CODE,
        self::KEY_PAGE_SNIPPET_CODES,
        self::KEY_CONTAINER_SNIPPETS,
    ];

    /**
     * @var SearchCriteria
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
     * @var SnippetContainer[]
     */
    private $containers;

    /**
     * @param SearchCriteria $productSelectionCriteria
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @param SnippetContainer[] $containers
     */
    private function __construct(
        SearchCriteria $productSelectionCriteria,
        string $rootSnippetCode,
        array $pageSnippetCodes,
        array $containers
    ) {
        $this->selectionCriteria = $productSelectionCriteria;
        $this->rootSnippetCode = $rootSnippetCode;
        $this->pageSnippetCodes = $pageSnippetCodes;
        $this->containers = $containers;
    }

    /**
     * @param SearchCriteria $selectionCriteria
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @param array[] $containerData
     * @return ProductListingSnippetContent
     */
    public static function create(
        SearchCriteria $selectionCriteria,
        string $rootSnippetCode,
        array $pageSnippetCodes,
        array $containerData
    ) : ProductListingSnippetContent {
        SnippetCodeValidator::validate($rootSnippetCode);
        if (! in_array($rootSnippetCode, $pageSnippetCodes)) {
            $pageSnippetCodes = array_merge([$rootSnippetCode], $pageSnippetCodes);
        }
        return new self(
            $selectionCriteria,
            $rootSnippetCode,
            $pageSnippetCodes,
            self::createSnippetContainers($containerData)
        );
    }

    /**
     * @param array[] $containerArray
     * @return SnippetContainer[]
     */
    private static function createSnippetContainers(array $containerArray) : array
    {
        return array_map(function ($code) use ($containerArray) {
            return new SnippetContainer($code, $containerArray[$code]);
        }, array_keys($containerArray));
    }

    public static function fromJson(string $json) : ProductListingSnippetContent
    {
        $pageInfo = self::decodeJson($json);
        self::validateRequiredKeysArePresent($pageInfo);

        self::validateProductListingSearchCriteria($pageInfo[self::KEY_CRITERIA]);
        $searchCriteria = self::createSearchCriteriaFromMetaInfo($pageInfo[self::KEY_CRITERIA]);

        return static::create(
            $searchCriteria,
            $pageInfo[self::KEY_ROOT_SNIPPET_CODE],
            $pageInfo[self::KEY_PAGE_SNIPPET_CODES],
            $pageInfo[self::KEY_CONTAINER_SNIPPETS]
        );
    }

    /**
     * @param mixed[] $pageInfo
     */
    protected static function validateRequiredKeysArePresent(array $pageInfo)
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
    private static function decodeJson(string $json) : array
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
    public function getInfo() : array
    {
        return [
            self::KEY_CRITERIA => $this->selectionCriteria,
            self::KEY_ROOT_SNIPPET_CODE => $this->rootSnippetCode,
            self::KEY_PAGE_SNIPPET_CODES => $this->pageSnippetCodes,
            self::KEY_CONTAINER_SNIPPETS => $this->getContainerSnippets(),
        ];
    }

    public function getSelectionCriteria() : SearchCriteria
    {
        return $this->selectionCriteria;
    }

    public function getRootSnippetCode() : string
    {
        return $this->rootSnippetCode;
    }

    /**
     * @return string[]
     */
    public function getPageSnippetCodes() : array
    {
        return $this->pageSnippetCodes;
    }

    /**
     * @param mixed[] $metaInfo
     * @return SearchCriteria
     */
    private static function createSearchCriteriaFromMetaInfo(array $metaInfo) : SearchCriteria
    {
        $criterionArray = array_map(function (array $criterionMetaInfo) {
            if (isset($criterionMetaInfo['condition'])) {
                return self::createSearchCriteriaFromMetaInfo($criterionMetaInfo);
            }

            $className = self::getCriterionClassNameForOperation($criterionMetaInfo['operation']);

            return new $className($criterionMetaInfo['fieldName'], $criterionMetaInfo['fieldValue']);
        }, $metaInfo['criteria']);

        return CompositeSearchCriterion::create($metaInfo['condition'], ...$criterionArray);
    }

    /**
     * @param mixed[] $metaInfo
     */
    private static function validateProductListingSearchCriteria(array $metaInfo)
    {
        if (! isset($metaInfo['condition'])) {
            throw new MalformedSearchCriteriaMetaException('Missing criteria condition.');
        }

        if (! isset($metaInfo['criteria'])) {
            throw new MalformedSearchCriteriaMetaException('Missing criteria.');
        }

        every($metaInfo['criteria'], function (array $criteria) {
            if (isset($criteria['condition'])) {
                self::validateProductListingSearchCriteria($criteria);
                return;
            }

            self::validateSearchCriterionMetaInfo($criteria);
        });
    }

    /**
     * @param mixed[] $criterionArray
     */
    private static function validateSearchCriterionMetaInfo(array $criterionArray)
    {
        if (! isset($criterionArray['fieldName'])) {
            throw new MalformedSearchCriteriaMetaException('Missing criterion field name.');
        }

        if (! isset($criterionArray['fieldValue'])) {
            throw new MalformedSearchCriteriaMetaException('Missing criterion field value.');
        }

        if (! isset($criterionArray['operation'])) {
            throw new MalformedSearchCriteriaMetaException('Missing criterion operation.');
        }

        if (! class_exists(self::getCriterionClassNameForOperation($criterionArray['operation']))) {
            throw new MalformedSearchCriteriaMetaException(
                sprintf('Unknown criterion operation "%s"', $criterionArray['operation'])
            );
        }
    }

    private static function getCriterionClassNameForOperation(string $operationName) : string
    {
        return SearchCriterion::class . $operationName;
    }

    /**
     * @return array[]
     */
    public function getContainerSnippets() : array
    {
        return array_reduce($this->containers, function ($carry, SnippetContainer $container) {
            return array_merge($carry, $container->toArray());
        }, []);
    }
}
