<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpRequest;

class Pagination
{
    const PAGINATION_QUERY_PARAMETER_NAME = 'p';

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var int
     */
    private $collectionSize;

    /**
     * @var int
     */
    private $numberOfItemsPerPage;

    /**
     * @var int
     */
    private $lazyLoadedCurrentPageNumber;

    /**
     * @param HttpRequest $request
     * @param int $collectionSize
     * @param int $numberOfItemsPerPage
     */
    private function __construct(HttpRequest $request, $collectionSize, $numberOfItemsPerPage)
    {
        $this->request = $request;
        $this->collectionSize = $collectionSize;
        $this->numberOfItemsPerPage = $numberOfItemsPerPage;
    }

    /**
     * @param HttpRequest $request
     * @param mixed $collectionSize
     * @param mixed $numberOfItemsPerPage
     * @return Pagination
     */
    public static function create(HttpRequest $request, $collectionSize, $numberOfItemsPerPage)
    {
        if (!is_int($collectionSize)) {
            throw new InvalidCollectionSizeTypeException(
                sprintf('Collection size for pagination should be an integer, got "%s".', gettype($collectionSize))
            );
        }

        if (!is_int($numberOfItemsPerPage)) {
            throw new InvalidNumberOfItemsPerPageTypeException(sprintf(
                'Number of items per page for pagination should be an integer, got "%s".',
                gettype($numberOfItemsPerPage)
            ));
        }

        return new self($request, $collectionSize, $numberOfItemsPerPage);
    }

    /**
     * @return int
     */
    public function getCollectionSize()
    {
        return $this->collectionSize;
    }

    /**
     * @return int
     */
    public function getNumberOfItemsPerPage()
    {
        return $this->numberOfItemsPerPage;
    }

    /**
     * @return int
     */
    public function getCurrentPageNumber()
    {
        if (null === $this->lazyLoadedCurrentPageNumber) {
            $n = (int) $this->request->getQueryParameter(self::PAGINATION_QUERY_PARAMETER_NAME);
            $this->lazyLoadedCurrentPageNumber = max(1, $n);
        }

        return $this->lazyLoadedCurrentPageNumber;
    }

    /**
     * @param int $pageNumber
     * @return string
     */
    public function getQueryStringForPage($pageNumber)
    {
        $queryParameters = $this->request->getQueryParametersExceptGiven(self::PAGINATION_QUERY_PARAMETER_NAME);
        $queryParameters[self::PAGINATION_QUERY_PARAMETER_NAME] = $pageNumber;

        return http_build_query($queryParameters);

    }
}
