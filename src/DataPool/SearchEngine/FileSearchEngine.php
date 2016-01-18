<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextVersion;
use LizardsAndPumpkins\Context\SelfContainedContextBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\Exception\SearchEngineNotAvailableException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Utils\LocalFilesystem;

class FileSearchEngine extends IntegrationTestSearchEngineAbstract
{
    const PRODUCT_ID = 'product_id';
    const CONTEXT = 'context';
    const FIELDS = 'fields';
    
    /**
     * @var string
     */
    private $storagePath;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FacetFieldTransformationRegistry
     */
    private $facetFieldTransformationRegistry;

    /**
     * @param string $storagePath
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FacetFieldTransformationRegistry $facetFieldTransformationRegistry
     */
    private function __construct(
        $storagePath,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FacetFieldTransformationRegistry $facetFieldTransformationRegistry
    ) {
        $this->storagePath = $storagePath;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->facetFieldTransformationRegistry = $facetFieldTransformationRegistry;
    }

    /**
     * @param string $storagePath
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FacetFieldTransformationRegistry $facetFieldTransformationRegistry
     * @return FileSearchEngine
     */
    public static function create(
        $storagePath,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FacetFieldTransformationRegistry $facetFieldTransformationRegistry
    ) {
        if (!is_writable($storagePath)) {
            throw new SearchEngineNotAvailableException(sprintf(
                'Directory "%s" is not writable by the filesystem search engine.',
                realpath($storagePath)
            ));
        }

        return new self($storagePath, $searchCriteriaBuilder, $facetFieldTransformationRegistry);
    }

    public function addDocument(SearchDocument $searchDocument)
    {
        $searchDocumentFilePath = $this->storagePath . '/' . $this->getSearchDocumentIdentifier($searchDocument);

        $searchDocumentArrayRepresentation = $this->getArrayRepresentationOfSearchDocument($searchDocument);
        $searchDocumentJson = json_encode($searchDocumentArrayRepresentation, JSON_PRETTY_PRINT);

        file_put_contents($searchDocumentFilePath, $searchDocumentJson);
    }

    /**
     * @return SearchDocument[]
     */
    final protected function getSearchDocuments()
    {
        $searchDocuments = [];

        $directoryIterator = new \DirectoryIterator($this->storagePath);

        foreach ($directoryIterator as $entry) {
            if (!$entry->isFile()) {
                continue;
            }

            $filePath = $this->storagePath . '/' . $entry->getFilename();
            $searchDocumentJson = file_get_contents($filePath);

            $searchDocuments[] = $this->createSearchDocumentFormJson($searchDocumentJson);
        }

        return $searchDocuments;
    }

    /**
     * @param SearchDocument $searchDocument
     * @return mixed[]
     */
    private function getArrayRepresentationOfSearchDocument(SearchDocument $searchDocument)
    {
        return [
            self::PRODUCT_ID => (string) $searchDocument->getProductId(),
            self::FIELDS => $this->getSearchDocumentFieldsAsArray($searchDocument->getFieldsCollection()),
            self::CONTEXT => $this->getContextAsArray($searchDocument->getContext())
        ];
    }

    /**
     * @param SearchDocumentFieldCollection $searchDocumentFieldCollection
     * @return string[]
     */
    private function getSearchDocumentFieldsAsArray(SearchDocumentFieldCollection $searchDocumentFieldCollection)
    {
        return array_reduce(
            $searchDocumentFieldCollection->getFields(),
            function ($searchDocumentFieldsArray, SearchDocumentField $field) {
                $searchDocumentFieldsArray[$field->getKey()] = $field->getValues();
                return $searchDocumentFieldsArray;
            }
        );
    }

    /**
     * @param Context $context
     * @return string[]
     */
    private function getContextAsArray(Context $context)
    {
        return array_reduce($context->getSupportedCodes(), function (array $carry, $contextCode) use ($context) {
            $carry[$contextCode] = $context->getValue($contextCode);
            return $carry;
        }, []);
    }

    /**
     * @param string $json
     * @return SearchDocument
     */
    private function createSearchDocumentFormJson($json)
    {
        $searchDocumentArrayRepresentation = json_decode($json, true);

        $context = $this->createContextFromDataSet($searchDocumentArrayRepresentation[self::CONTEXT]);
        $searchDocumentFields = SearchDocumentFieldCollection::fromArray(
            $searchDocumentArrayRepresentation[self::FIELDS]
        );
        $productId = ProductId::fromString($searchDocumentArrayRepresentation[self::PRODUCT_ID]);

        return new SearchDocument($searchDocumentFields, $context, $productId);
    }

    /**
     * @param string[] $contextDataSet
     * @return Context
     */
    private function createContextFromDataSet($contextDataSet)
    {
        $contextDataSet[ContextVersion::CODE] = '-1';
        return SelfContainedContextBuilder::rehydrateContext($contextDataSet);
    }

    public function clear()
    {
        (new LocalFilesystem())->removeDirectoryContents($this->storagePath);
    }

    /**
     * @return SearchCriteriaBuilder
     */
    final protected function getSearchCriteriaBuilder()
    {
        return $this->searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    final protected function getFacetFieldTransformationRegistry()
    {
        return $this->facetFieldTransformationRegistry;
    }
}
