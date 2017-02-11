<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Import\XPathParser;

class CatalogFixtureFileQuery
{
    private static function getSharedFixtureFileDirectory()
    {
        return __DIR__ . '/../../shared-fixture/';
    }

    public static function getPathToFixtureFile(string $fixtureFile)
    {
        return self::getSharedFixtureFileDirectory() . $fixtureFile;
    }
    
    public static function getSkuOfFirstSimpleProductInFixture(string $fixtureFile) : string
    {
        $xml = file_get_contents(self::getPathToFixtureFile($fixtureFile));
        $parser = new XPathParser($xml);
        $skuNode = $parser->getXmlNodesArrayByXPath('//catalog/products/product[@type="simple"][1]/@sku');
        return $skuNode[0]['value'];
    }

    public static function getSkuOfFirstConfigurableProductInFixture(string $fixtureFile) : string
    {
        $xml = file_get_contents(self::getPathToFixtureFile($fixtureFile));
        $parser = new XPathParser($xml);
        $skuNode = $parser->getXmlNodesArrayByXPath('//catalog/products/product[@type="configurable"][1]/@sku');
        return $skuNode[0]['value'];
    }
}
