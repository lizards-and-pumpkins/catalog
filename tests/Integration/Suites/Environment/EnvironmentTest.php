<?php

namespace Brera\Environment;

use Brera\IntegrationTestFactory;
use Brera\PoCMasterFactory;
use Brera\CommonFactory;
use Brera\Product\ProductSourceBuilder;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldCreateTheDecoratedEnvironmentSet()
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
        $factory->register(new IntegrationTestFactory()); // is this needed?
        /** @var EnvironmentSourceBuilder $environmentSourceBuilder */
        $environmentSourceBuilder = $factory->createEnvironmentSourceBuilder();
        /** @var ProductSourceBuilder $productSourceBuilder */
        $productSourceBuilder = $factory->createProductSourceBuilder();
        /** @var EnvironmentSource $environmentSource */
        $environmentSource = $environmentSourceBuilder->createFromXml($xml);
        $productSource = $productSourceBuilder->createProductSourceFromXml($xml);
        $codes = ['website', 'language', 'version'];
        $extractedValues = [];
        $environmentCounter = 0;
        /** @var Environment $environment */
        foreach ($environmentSource->extractEnvironments($codes) as $environment) {
            $environmentCounter++;
            $this->assertEquals($codes, $environment->getSupportedCodes());
            $expected = $environment->getValue('website') . '-' . $environment->getValue('language');
            $product = $productSource->getProductForEnvironment($environment);
            $attributeValue = $product->getAttributeValue('name');
            $this->assertEquals($expected, $attributeValue);
            $extractedValues[] = $attributeValue;
        }
        $this->assertEquals(4 * 4, $environmentCounter, "The cartesian product of environment values should be 16.");
        $this->assertCount(4, array_unique($extractedValues), "There should be 4 unique values.");
    }
}
