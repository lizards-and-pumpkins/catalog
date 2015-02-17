<?php


namespace Brera\Context;

use Brera\InputXmlIsEmptyStringException;
use Brera\InvalidXmlTypeException;
use Brera\XPathParser;

class ContextSourceBuilder
{

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    public function __construct(ContextBuilder $contextBuilder)
    {
        $this->contextBuilder = $contextBuilder;
    }
    
    /**
     * @param string $xml
     * @return ContextSource
     */
    public function createFromXml($xml)
    {
        $this->validateXmlString($xml);
        $contexts = $this->extractAttributesFromXml($xml);
        return new ContextSource($contexts, $this->contextBuilder);
    }

    /**
     * @param string $xml
     * @throws InvalidXmlTypeException
     * @throws InputXmlIsEmptyStringException
     */
    private function validateXmlString($xml)
    {
        if (!is_string($xml)) {
            throw new InvalidXmlTypeException('The XML data has to be passed as a string');
        }
        if (empty($xml)) {
            throw new InputXmlIsEmptyStringException('The input XML data is empty.');
        }
    }

    /**
     * @param string $xml
     * @return string[]
     */
    private function extractAttributesFromXml($xml)
    {
        $contexts = [];
        $parser = new XPathParser($xml);

        $attributes = $parser->getXmlNodesArrayByXPath('//product/attributes//@*');
        foreach ($attributes as $attribute) {
            $contexts[$attribute['nodeName']][] = $attribute['value'];
        }

        return $contexts;
    }
}
