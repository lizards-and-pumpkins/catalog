<?php

namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\IntegrationTestFactory;
use LizardsAndPumpkins\MasterFactory;
use LizardsAndPumpkins\SampleMasterFactory;
use LizardsAndPumpkins\CommonFactory;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MasterFactory
     */
    private $factory;
    
    protected function setUp()
    {
        $this->factory = new SampleMasterFactory();
        $this->factory->register(new CommonFactory());
        $this->factory->register(new IntegrationTestFactory());
    }
    
    public function testDecoratedContextSetIsCreated()
    {
        $xml = <<<EOX
<product sku="test" type="simple"><attributes>
    <name website="ru" locale="de_DE">ru-de_DE</name>
    <name website="ru" locale="en_US">ru-en_US</name>
    <name website="cy" locale="de_DE">cy-de_DE</name>
    <name website="cy" locale="en_US">cy-en_US</name>
</attributes></product>
EOX;
        $productXmlToProductBuilder = $this->factory->createProductXmlToProductBuilderLocator();
        $contextSource = $this->factory->createContextSource();
        $productBuilder = $productXmlToProductBuilder->createProductBuilderFromXml($xml);
        $codes = ['website', 'locale', 'version'];
        $extractedValues = [];
        $contextCounter = 0;

        foreach ($contextSource->getAllAvailableContexts() as $context) {
            $contextCounter++;
            $this->assertEmpty(array_diff($codes, $context->getSupportedCodes()));
            $expected = $context->getValue('website') . '-' . $context->getValue('locale');
            $product = $productBuilder->getProductForContext($context);
            $attributeValue = $product->getFirstValueOfAttribute('name');
            $this->assertEquals($expected, $attributeValue);
            $extractedValues[] = $attributeValue;
        }

        $this->assertCount(4, array_unique($extractedValues), 'There should be 4 unique values.');
    }

    public function testContextCanBeSerializedAndRehydrated()
    {
        /** @var ContextSource $contextSource */
        /** @var ContextBuilder $contextBuilder */
        /** @var Context $context */
        $contextSource = $this->factory->createContextSource();
        $contextBuilder = $this->factory->createContextBuilder();
        foreach ($contextSource->getAllAvailableContexts() as $context) {
            $jsonString = json_encode($context);
            $rehydratedContext = $contextBuilder->createContext(json_decode($jsonString, true));
            $this->assertSame((string) $context, (string) $rehydratedContext);
        }
    }
}
