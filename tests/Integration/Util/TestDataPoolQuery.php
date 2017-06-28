<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class TestDataPoolQuery
{
    public static function getProductJsonSnippetForId(
        MasterFactory $masterFactory,
        string $productId,
        string $version = '-1'
    ): string {
        $key = self::getProductJsonSnippetKeyForId($masterFactory, $productId, $version);

        return self::getSnippetFromDataPool($masterFactory, $key);
    }

    private static function getProductJsonSnippetKeyForId(
        MasterFactory $masterFactory,
        string $productIdString,
        string $version
    ): string {
        $keyGenerator = $masterFactory->createProductJsonSnippetKeyGenerator();
        $context = $masterFactory->createContextBuilder()->createContext([DataVersion::CONTEXT_CODE => $version]);

        return $keyGenerator->getKeyForContext($context, ['product_id' => $productIdString]);
    }

    private static function getSnippetFromDataPool(MasterFactory $masterFactory, string $key): string
    {
        return $masterFactory->createDataPoolReader()->getSnippet($key);
    }
}
