<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class TestDataPoolQuery
{
    public static function getProductJsonSnippetForId(MasterFactory $factory, string $productIdString, string $version = '-1'): string
    {
        $key = self::getProductJsonSnippetKeyForId($factory, $productIdString, $version);

        return self::getSnippetFromDataPool($factory, $key);
    }

    private static function getProductJsonSnippetKeyForId(MasterFactory $factory, string $productIdString, $version): string
    {
        $keyGenerator = $factory->createProductJsonSnippetKeyGenerator();
        $context = $factory->createContextBuilder()->createContext([DataVersion::CONTEXT_CODE => $version]);
        return $keyGenerator->getKeyForContext($context, ['product_id' => $productIdString]);
    }

    private static function getSnippetFromDataPool(MasterFactory $factory, string $key): string
    {
        return $factory->createDataPoolReader()->getSnippet($key);
    }
}
