<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Renderer\Block;
use LizardsAndPumpkins\Renderer\InvalidDataObjectException;

class PaginationBlock extends Block
{
    /**
     * @var Pagination
     */
    private $lazyLoadedValidatedDataObject;

    /**
     * @var int
     */
    private $lazyLoadedTotalPageCount;

    /**
     * @return int
     */
    public function getTotalPageCount()
    {
        if (null === $this->lazyLoadedTotalPageCount) {
            $this->lazyLoadedTotalPageCount = ceil($this->getCollectionSize() / $this->getNumberOfItemsPerPage());
        }

        return $this->lazyLoadedTotalPageCount;
    }

    /**
     * @return int
     */
    public function getCurrentPageNumber()
    {
        $dataObject = $this->getValidatedDataObject();
        return $dataObject->getCurrentPageNumber();
    }

    /**
     * @param int $pageNumber
     * @return string
     */
    public function getQueryStringForPage($pageNumber)
    {
        $dataObject = $this->getValidatedDataObject();
        return $dataObject->getQueryStringForPage($pageNumber);
    }

    /**
     * @return int
     */
    private function getCollectionSize()
    {
        $dataObject = $this->getValidatedDataObject();
        return $dataObject->getCollectionSize();
    }

    /**
     * @return int
     */
    private function getNumberOfItemsPerPage()
    {
        $dataObject = $this->getValidatedDataObject();
        return $dataObject->getNumberOfItemsPerPage();
    }

    /**
     * @return Pagination
     */
    private function getValidatedDataObject()
    {
        if (null === $this->lazyLoadedValidatedDataObject) {
            $this->validateDataObject();
            $this->lazyLoadedValidatedDataObject = $this->getDataObject();
        }

        return $this->lazyLoadedValidatedDataObject;
    }

    private function validateDataObject()
    {
        $dataObject = $this->getDataObject();

        if (!($dataObject instanceof Pagination)) {
            throw new InvalidDataObjectException(sprintf(
                'Data object must be instance of PaginationData, got "%s".',
                $this->getVariableType($dataObject)
            ));
        }
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private function getVariableType($variable)
    {
        return 'object' !== gettype($variable) ?
            gettype($variable) :
            get_class($variable);
    }
}
