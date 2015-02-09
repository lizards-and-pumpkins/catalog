<?php

namespace Brera\Environment;

use Brera\PoCMasterFactory;
use Brera\IntegrationTestFactory;
use Brera\Product\ProductSourceBuilder;

/**
 * @coversNothing
 */
class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldCreateTheDecoratedEnvironmentSet()
    {
        $xml = <<<EOX
<product sku="test"><attributes>
    <attribute code="name" website="web1" language="lang1">web1-lang1</attribute>
    <attribute code="name" website="web1" language="lang2">web1-lang2</attribute>
    <attribute code="name" website="web2" language="lang1">web2-lang1</attribute>
    <attribute code="name" website="web2" language="lang2">web2-lang2</attribute>
</attributes></product>
EOX;
        $factory = new PoCMasterFactory();
        $factory->register(new IntegrationTestFactory());
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
