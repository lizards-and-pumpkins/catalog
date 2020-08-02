<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\SnippetContainer;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingRequestHandler;
use LizardsAndPumpkins\ProductListing\Import\Exception\MalformedSearchCriteriaMetaException;
use LizardsAndPumpkins\Util\SnippetCodeValidator;

class ProductListingMetaSnippetContent implements PageMetaInfoSnippetContent
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
     * @var array[]
     */
    private $pageSpecificData;

    /**
     * @param SearchCriteria $productSelectionCriteria
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @param SnippetContainer[] $containers
     * @param array[] $pageSpecificData
     */
    private function __construct(
        SearchCriteria $productSelectionCriteria,
        string $rootSnippetCode,
        array $pageSnippetCodes,
        array $containers,
        array $pageSpecificData
    ) {
        $this->selectionCriteria = $productSelectionCriteria;
        $this->rootSnippetCode = $rootSnippetCode;
        $this->pageSnippetCodes = $pageSnippetCodes;
        $this->containers = $containers;
        $this->pageSpecificData = $pageSpecificData;
    }

    /**
     * @param SearchCriteria $selectionCriteria
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @param array[] $containerData
     * @param array[] $pageSpecificData
     * @return ProductListingMetaSnippetContent
     */
    public static function create(
        SearchCriteria $selectionCriteria,
        string $rootSnippetCode,
        array $pageSnippetCodes,
        array $containerData,
        array $pageSpecificData
    ) : ProductListingMetaSnippetContent {
        SnippetCodeValidator::validate($rootSnippetCode);

        if (! in_array($rootSnippetCode, $pageSnippetCodes)) {
            $pageSnippetCodes = array_merge([$rootSnippetCode], $pageSnippetCodes);
        }

        $snippetContainers = self::createSnippetContainers($containerData);

        return new self($selectionCriteria, $rootSnippetCode, $pageSnippetCodes, $snippetContainers, $pageSpecificData);
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

    /**
     * @param mixed[] $pageMeta
     * @return ProductListingSnippetContent
     */
    public static function fromArray(array $pageMeta) : ProductListingMetaSnippetContent
    {
        self::validateRequiredKeysArePresent($pageMeta);

        self::validateProductListingSearchCriteria($pageMeta[self::KEY_CRITERIA]);
        $searchCriteria = self::createSearchCriteriaFromMetaInfo($pageMeta[self::KEY_CRITERIA]);

        return static::create(
            $searchCriteria,
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
        foreach (self::$requiredKeys as $key) {
            if (! array_key_exists($key, $pageInfo)) {
                throw new \RuntimeException(sprintf('Missing key in input array: "%s"', $key));
            }
        }
    }

    /**
     * @return mixed[]
     */
    public function toArray() : array
    {
        return [
            self::KEY_HANDLER_CODE => ProductListingRequestHandler::CODE,
            self::KEY_CRITERIA => $this->selectionCriteria,
            self::KEY_ROOT_SNIPPET_CODE => $this->rootSnippetCode,
            self::KEY_PAGE_SNIPPET_CODES => $this->pageSnippetCodes,
            self::KEY_CONTAINER_SNIPPETS => $this->getContainerSnippets(),
            self::KEY_PAGE_SPECIFIC_DATA => $this->pageSpecificData,
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
    private static function validateProductListingSearchCriteria(array $metaInfo): void
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
    private static function validateSearchCriterionMetaInfo(array $criterionArray): void
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
        return preg_replace('/Criteria$/', 'Criterion', SearchCriteria::class) . $operationName;
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

    /**
     * @return array[]
     */
    public function getPageSpecificData(): array
    {
        return $this->pageSpecificData;
    }
}
