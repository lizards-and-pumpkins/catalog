<?php

namespace Brera\Context;

use Brera\IntegrationTestFactory;
use Brera\PoCMasterFactory;
use Brera\CommonFactory;
use Brera\Product\ProductSourceBuilder;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    public function testDecoratedContextSetIsCreated()
    {
        $xml = <<<EOX
<product sku="test"><attributes>
    <name website="ru" language="de_DE">ru-de_DE</name>
    <name website="ru" language="en_US">ru-en_US</name>
    <name website="cy" language="de_DE">cy-de_DE</name>
    <name website="cy" language="en_US">cy-en_US</name>
</attributes></product>
EOX;
        $factory = new PoCMasterFactory();
        $factory->register(new CommonFactory());
        $factory->register(new IntegrationTestFactory());
        /** @var ProductSourceBuilder $productSourceBuilder */
        $productSourceBuilder = $factory->createProductSourceBuilder();
        $contextSource = $factory->createContextSource();
        $productSource = $productSourceBuilder->createProductSourceFromXml($xml);
        $codes = ['website', 'language', 'version'];
        $extractedValues = [];
        $contextCounter = 0;

        foreach ($contextSource->getAllAvailableContexts() as $context) {
            $contextCounter++;
            $this->assertEmpty(array_diff($codes, $context->getSupportedCodes()));
            $expected = $context->getValue('website') . '-' . $context->getValue('language');
            $product = $productSource->getProductForContext($context);
            $attributeValue = $product->getAttributeValue('name');
            $this->assertEquals($expected, $attributeValue);
            $extractedValues[] = $attributeValue;
        }

        $this->assertCount(4, array_unique($extractedValues), 'There should be 4 unique values.');
    }
}
