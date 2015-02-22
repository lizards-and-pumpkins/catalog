<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;

class FileSearchEngine implements SearchEngine
{
    /**
     * @var string
     */
    private $storagePath;

    public function __construct($storagePath = null)
    {
        if (is_null($storagePath)) {
            $storagePath = sys_get_temp_dir();
        }

        if (!is_writable($storagePath)) {
            throw new SearchEngineNotAvailableException(sprintf(
                'Directory "%s" is not writable by the filesystem search engine.', $storagePath
            ));
        }

        $this->storagePath = $storagePath;
    }

    /**
     * @param SearchDocument $searchDocument
     * @return void
     */
    public function addSearchDocument(SearchDocument $searchDocument)
    {
        file_put_contents($this->storagePath . DIRECTORY_SEPARATOR . uniqid(), serialize($searchDocument));
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

        foreach ($directoryIterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $filePath = $this->storagePath . DIRECTORY_SEPARATOR . $file->getFilename();

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
