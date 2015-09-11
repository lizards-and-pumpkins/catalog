<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use Brera\DataVersion;
use Brera\Product\ProductId;
use Brera\Utils\Clearable;
use Brera\Utils\LocalFilesystem;

class FileSearchEngine extends IntegrationTestSearchEngineAbstract implements Clearable
{
    /**
     * @var string
     */
    private $storagePath;

    /**
     * @param string $storagePath
     */
    private function __construct($storagePath)
    {
        $this->storagePath = $storagePath;
    }

    /**
     * @param string $storagePath
     * @return FileSearchEngine
     */
    public static function create($storagePath)
    {
        if (!is_writable($storagePath)) {
            throw new SearchEngineNotAvailableException(sprintf(
                'Directory "%s" is not writable by the filesystem search engine.',
                realpath($storagePath)
            ));
        }

        return new self($storagePath);
    }

    public function addSearchDocument(SearchDocument $searchDocument)
    {
        $searchDocumentFilePath = $this->storagePath . '/' . $this->getSearchDocumentIdentifier($searchDocument);

        $searchDocumentArrayRepresentation = $this->getArrayRepresentationOfSearchDocument($searchDocument);
        $searchDocumentJson = json_encode($searchDocumentArrayRepresentation, JSON_PRETTY_PRINT);

        file_put_contents($searchDocumentFilePath, $searchDocumentJson);
    }

    /**
     * @return SearchDocument[]
     */
    protected function getSearchDocuments()
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

    public function flush()
    {
        
    }

    /**
     * @param SearchDocument $searchDocument
     * @return mixed[]
     */
    private function getArrayRepresentationOfSearchDocument(SearchDocument $searchDocument)
    {
        return [
            'product_id' => (string) $searchDocument->getProductId(),
            'fields'     => $this->getSearchDocumentFieldsAsArray($searchDocument->getFieldsCollection()),
            'context'    => $this->getContextAsArray($searchDocument->getContext())
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
                $searchDocumentFieldsArray[$field->getKey()] = $field->getValue();
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
        $contextArray = [];

        foreach ($context->getSupportedCodes() as $contextCode) {
            $contextArray[$contextCode] = $context->getValue($contextCode);
        }

        return $contextArray;
    }

    /**
     * @param string $json
     * @return SearchDocument
     */
    private function createSearchDocumentFormJson($json)
    {
        $searchDocumentArrayRepresentation = json_decode($json, true);

        $context = $this->createContextFromDataSet($searchDocumentArrayRepresentation['context']);
        $searchDocumentFields = SearchDocumentFieldCollection::fromArray($searchDocumentArrayRepresentation['fields']);
        $productId = ProductId::fromString($searchDocumentArrayRepresentation['product_id']);

        return new SearchDocument($searchDocumentFields, $context, $productId);
    }

    /**
     * @param string[] $dataSet
     * @return Context
     */
    private function createContextFromDataSet($dataSet)
    {
        $dataVersion = DataVersion::fromVersionString($dataSet['version']);
        $contextBuilder = new ContextBuilder($dataVersion);

        unset($dataSet['version']);

        return $contextBuilder->createContext($dataSet);
    }

    public function clear()
    {
        (new LocalFilesystem())->removeDirectoryContents($this->storagePath);
    }
}
