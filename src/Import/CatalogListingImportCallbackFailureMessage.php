<?php

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

    /**
     * @param \Exception $exception
     * @param string $listingXml
     */
    public function __construct(\Exception $exception, $listingXml)
    {
        $this->exception = $exception;
        $this->listingXml = $listingXml;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'An error occurred while processing catalog XML import listing callbacks: %s',
            $this->exception->getMessage()
        );
    }

    /**
     * @return mixed[]
     */
    public function getContext()
    {
        return [
            'exception' => $this->exception,
            'listing_xml' => $this->listingXml
        ];
    }

    /**
     * @return string
     */
    public function getContextSynopsis()
    {
        return sprintf('File %s:%d', $this->exception->getFile(), $this->exception->getLine());
    }
}
