<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

class ProductUrlKeyStoreTest extends AbstractIntegrationTest
{
    public function testUrlKeysAreWrittenToStore()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();
        
        $this->importCatalogFixture($factory, 'simple_product_armflasher-v1.xml');

        $this->failIfMessagesWhereLogged($factory->getLogger());
        
        $dataPoolReader = $factory->createDataPoolReader();

        $currentVersion = $dataPoolReader->getCurrentDataVersion();
        $urlKeys = $dataPoolReader->getUrlKeysForVersion($currentVersion);
        $this->assertNotEmpty($urlKeys);
    }
}
