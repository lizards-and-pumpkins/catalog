<?php

namespace Brera;

use Brera\Context\Context;

class RootSnippetSourceList
{
    /**
     * @var RootSnippetSource[]
     */
    private $sources;

    /**
     * @param array[] $sources
     */
    private function __construct($sources)
    {
        $this->sources = $sources;
    }

    /**
     * @param array[] $sourceDataPairs
     * @return RootSnippetSourceList
     */
    public static function fromArray(array $sourceDataPairs)
    {
        $sources = [];

        foreach ($sourceDataPairs as $sourceDataPair) {
            self::validateSourceData($sourceDataPair);

            $sources[] = new RootSnippetSource($sourceDataPair['context'], $sourceDataPair['numItemsPerPage']);
        }

        return new self($sources);
    }

    /**
     * @param Context $context
     * @return int[]
     */
    public function getListOfAvailableNumberOfItemsPerPageForContext(Context $context)
    {
        $numItemsPerPage = [];

        foreach ($this->sources as $source) {
            if ($source->getContext() == $context) {
                $numItemsPerPage[] = $source->getNumItemsPerPage();
            }
        }

        return $numItemsPerPage;
    }

    /**
     * @param mixed[] $sourceDataPair
     * @throws InvalidRootSnippetSourceDataException
     */
    private static function validateSourceData(array $sourceDataPair)
    {
        if (!array_key_exists('context', $sourceDataPair) || !is_a($sourceDataPair['context'], Context::class)) {
            throw new InvalidRootSnippetSourceDataException(
                'No valid context found in one or more root snippet source data pairs.'
            );
        }

        if (!array_key_exists('numItemsPerPage', $sourceDataPair) || !is_int($sourceDataPair['numItemsPerPage'])) {
            throw new InvalidRootSnippetSourceDataException(
                'No valid number of items per page found in one or more root snippet source data pairs.'
            );
        }
    }
}
