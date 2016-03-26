<?php


namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Import\XPathParser;

class ProductImportCallbackFailureMessage implements LogMessage
{
    private $unknownSku = '- unknown -';

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @var string
     */
    private $productXml;

    /**
     * @param \Exception $exception
     * @param string $productXml
     */
    public function __construct(\Exception $exception, $productXml)
    {
        $this->exception = $exception;
        $this->productXml = $productXml;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'Error during processing catalog product XML import for product "%s": %s',
            $this->getSkuFromProductXml(),
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
            'product_xml' => $this->productXml
        ];
    }

    /**
     * @return string
     */
    private function getSkuFromProductXml()
    {
        try {
            $node = (new XPathParser($this->productXml))->getXmlNodesArrayByXPath('/product/@sku');
        } catch (\Exception $e) {
        }
        return isset($node) && $node ?
            $node[0]['value'] :
            $this->unknownSku;
    }

    /**
     * @return string
     */
    public function getContextSynopsis()
    {
        return sprintf('File: %s:%d', $this->exception->getFile(), $this->exception->getLine());
    }
}
