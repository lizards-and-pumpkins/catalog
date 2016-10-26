<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Logging\LogMessage;

class CatalogListingImportCallbackFailureMessage implements LogMessage
{
    /**
     * @var \Exception
     */
    private $exception;
    
    /**
     * @var string
     */
    private $listingXml;

    public function __construct(\Exception $exception, string $listingXml)
    {
        $this->exception = $exception;
        $this->listingXml = $listingXml;
    }

    public function __toString() : string
    {
        return sprintf(
            'An error occurred while processing catalog XML import listing callbacks: %s',
            $this->exception->getMessage()
        );
    }

    /**
     * @return mixed[]
     */
    public function getContext() : array
    {
        return [
            'exception' => $this->exception,
            'listing_xml' => $this->listingXml
        ];
    }

    public function getContextSynopsis(): string
    {
        return sprintf('File %s:%d', $this->exception->getFile(), $this->exception->getLine());
    }
}
