<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;

class FileSearchEngine implements SearchEngine
{
    /**
     * @var string
     */
    private $storagePath;

    private final function __construct($storagePath)
    {
        if (!is_writable($storagePath)) {
            throw new SearchEngineNotAvailableException(sprintf(
                'Directory "%s" is not writable by the filesystem search engine.', realpath($storagePath)
            ));
        }

        $this->storagePath = $storagePath;
    }

    /**
     * @param $storagePath
     * @return FileSearchEngine
     */
    public static function withPath($storagePath)
    {
        return new self($storagePath);
    }

    /**
     * @return FileSearchEngine
     */
    public static function withDefaultPath()
    {
        $defaultPath = sys_get_temp_dir();

        return new self($defaultPath);
    }

    /**
     * @param SearchDocument $searchDocument
     * @return void
     */
    public function addSearchDocument(SearchDocument $searchDocument)
    {
        file_put_contents($this->storagePath . '/' . uniqid(), serialize($searchDocument));
    }

    /**
     * @param SearchDocumentCollection $searchDocumentCollection
     * @return void
     */
    public function addSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection)
    {
        foreach ($searchDocumentCollection->getDocuments() as $searchDocument) {
            $this->addSearchDocument($searchDocument);
        }

    }

    /**
     * @param string $queryString
     * @param Context $context
     * @return string[]
     */
    public function query($queryString, Context $context)
    {
        $results = [];

        $directoryIterator = new \DirectoryIterator($this->storagePath);

        foreach ($directoryIterator as $entry) {
            if (! $entry->isFile()) {
                continue;
            }

            $filePath = $this->storagePath . '/' . $entry->getFilename();

            /** @var SearchDocument $searchDocument */
            $searchDocument = unserialize(file_get_contents($filePath));

            if ($context != $searchDocument->getContext()) {
                continue;
            }

            $searchDocumentFieldsCollection = $searchDocument->getFieldsCollection();

            foreach ($searchDocumentFieldsCollection->getFields() as $field) {

                if (!in_array($searchDocument->getContent(), $results)) {
                    if (false !== stripos($field->getValue(), $queryString)) {
                        array_push($results, $searchDocument->getContent());
                    }
                }
            }
        }

        return $results;
    }
}
