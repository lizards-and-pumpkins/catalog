<?php


namespace Brera;


class EnvironmentSourceBuilder
{
    /**
     * @var DataVersion
     */
    private $dataVersion;

    /**
     * @var EnvironmentBuilder
     */
    private $environmentBuilder;

    public function __construct(DataVersion $dataVersion, EnvironmentBuilder $environmentBuilder)
    {
        $this->dataVersion = $dataVersion;
        $this->environmentBuilder = $environmentBuilder;
    }
    
    /**
     * @param string $xml
     * @return EnvironmentSource
     */
    public function createFromXml($xml)
    {
        $this->validateXmlString($xml);
        $environments = $this->extractAttributesFromXml($xml);
        $environments[VersionedEnvironment::CODE][] = $this->dataVersion;
        return new EnvironmentSource($environments, $this->environmentBuilder);
    }

    /**
     * @param string $xml
     * @throws InvalidXmlTypeException
     * @throws InputXmlIsEmptyStringException
     */
    private function validateXmlString($xml)
    {
        if (!is_string($xml)) {
            throw new InvalidXmlTypeException("The XML data has to be passed as a string");
        }
        if (empty($xml)) {
            throw new InputXmlIsEmptyStringException('The input XML data is empty.');
        }
    }

    /**
     * @param string $xml
     * @return array
     */
    private function extractAttributesFromXml($xml)
    {
        $environments = [];
        $parser = new XPathParser($xml);

        $attributes = $parser->getXmlNodesArrayByXPath('//product/attributes/*');
        foreach ($attributes as $attribute) {
            foreach ($attribute['attributes'] as $key => $value) {
                $environments[$key][] = $value;
            }
        }
        return $environments;
    }
}
