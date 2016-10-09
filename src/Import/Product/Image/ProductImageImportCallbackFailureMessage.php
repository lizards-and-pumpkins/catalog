<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Logging\LogMessage;

class ProductImageImportCallbackFailureMessage implements LogMessage
{
    /**
     * @var \Exception
     */
    private $exception;
    
    /**
     * @var string
     */
    private $productImageXml;

    public function __construct(\Exception $exception, string $productImageXml)
    {
        $this->exception = $exception;
        $this->productImageXml = $productImageXml;
    }

    public function __toString() : string
    {
        return sprintf(
            'Error during processing catalog product image XML import callback: %s',
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
            'product_image_xml' => $this->productImageXml
        ];
    }

    public function getContextSynopsis() : string
    {
        $exceptionSynopsis = sprintf('File: %s:%d', $this->exception->getFile(), $this->exception->getLine());
        $xmlSynopsis = sprintf('Image XML: %s', str_replace(["\n", "\r"], ' ', $this->productImageXml));
        return preg_replace('/  +/', ' ', $exceptionSynopsis . "\t" . $xmlSynopsis);
    }
}
