<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Log\LogMessage;

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

    /**
     * @param \Exception $exception
     * @param string $productImageXml
     */
    public function __construct(\Exception $exception, $productImageXml)
    {
        $this->exception = $exception;
        $this->productImageXml = $productImageXml;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'Error during processing catalog product image XML import callback: %s',
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
            'product_image_xml' => $this->productImageXml
        ];
    }

    /**
     * @return string
     */
    public function getContextSynopsis()
    {
        $exceptionSynopsis = sprintf('File: %s:%d', $this->exception->getFile(), $this->exception->getLine());
        $xmlSynopsis = sprintf('Image XML: %s', str_replace(["\n", "\r"], ' ', $this->productImageXml));
        return preg_replace('/  +/', ' ', $exceptionSynopsis . "\t" . $xmlSynopsis);
    }
}
