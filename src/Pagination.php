<?php

namespace Brera;

use Brera\Http\HttpRequest;

class Pagination
{
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
     * @return HttpRequest
     */
    public function getRequest()
    {
        return $this->request;
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
}
