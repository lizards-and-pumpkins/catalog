<?php

namespace Brera\Context;

use Brera\IntegrationTestFactory;
use Brera\PoCMasterFactory;
use Brera\CommonFactory;
use Brera\Product\ProductSourceBuilder;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldCreateTheDecoratedContextSet()
    {
        $xml = <<<EOX
<product sku="test"><attributes>
    <name website="web1" language="lang1">web1-lang1</name>
    <name website="web1" language="lang2">web1-lang2</name>
    <name website="web2" language="lang1">web2-lang1</name>
    <name website="web2" language="lang2">web2-lang2</name>
</attributes></product>
EOX;
        $factory = new PoCMasterFactory();
        $factory->register(new CommonFactory());
        $factory->register(new IntegrationTestFactory());
        /** @var ContextSourceBuilder $contextSourceBuilder */
        $contextSourceBuilder = $factory->createContextSourceBuilder();
        /** @var ProductSourceBuilder $productSourceBuilder */
        $productSourceBuilder = $factory->createProductSourceBuilder();
        /** @var ContextSource $contextSource */
        $contextSource = $contextSourceBuilder->createFromXml($xml);
        $productSource = $productSourceBuilder->createProductSourceFromXml($xml);
        $codes = ['website', 'language', 'version'];
        $extractedValues = [];
        $contextCounter = 0;
        /** @var Context $context */
        foreach ($contextSource->extractContexts($codes) as $context) {
            $contextCounter++;
            $this->assertEquals($codes, $context->getSupportedCodes());
            $expected = $context->getValue('website') . '-' . $context->getValue('language');
            $product = $productSource->getProductForContext($context);
            $attributeValue = $product->getAttributeValue('name');
            $this->assertEquals($expected, $attributeValue);
            $extractedValues[] = $attributeValue;
        }
        $this->assertEquals(4 * 4, $contextCounter, "The cartesian product of context values should be 16.");
        $this->assertCount(4, array_unique($extractedValues), "There should be 4 unique values.");
    }
}
