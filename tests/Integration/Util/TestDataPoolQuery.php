<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Util\Factory\MasterFactory;

class TestDataPoolQuery
{
    public static function getProductJsonSnippetForId(MasterFactory $factory, string $productIdString) : string
    {
        $key = self::getProductJsonSnippetKeyForId($factory, $productIdString);

        return self::getSnippetFromDataPool($factory, $key);
    }

    public static function getProductJsonSnippetKeyForId(MasterFactory $factory, string $productIdString) : string
    {
        $keyGenerator = $factory->createProductJsonSnippetKeyGenerator();
        $context = $factory->createContextBuilder()->createContext([]);
        return $keyGenerator->getKeyForContext($context, ['product_id' => $productIdString]);
    }

    private static function getSnippetFromDataPool(MasterFactory $factory, string $key) : string
    {
        return $factory->createDataPoolReader()->getSnippet($key);
    }
}
