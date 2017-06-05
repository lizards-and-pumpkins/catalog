<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\DataVersion\ContextVersion;
use LizardsAndPumpkins\DataPool\CurrentDataVersion;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class TestDataPoolQuery
{
    public static function getProductJsonSnippetForId(MasterFactory $factory, string $productIdString, string $version): string
    {
        $key = self::getProductJsonSnippetKeyForId($factory, $productIdString, $version);

        return self::getSnippetFromDataPool($factory, $key);
    }

    private static function getProductJsonSnippetKeyForId(MasterFactory $factory, string $productIdString, $version): string
    {
        $keyGenerator = $factory->createProductJsonSnippetKeyGenerator();
        $context = $factory->createContextBuilder()->createContext(['version' => $version]);
        return $keyGenerator->getKeyForContext($context, ['product_id' => $productIdString]);
    }

    private static function getSnippetFromDataPool(MasterFactory $factory, string $key): string
    {
        return $factory->createDataPoolReader()->getSnippet($key);
    }
}
