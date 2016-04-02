<?php

namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

/**
 * @covers \LizardsAndPumpkins\DataPool\UrlKeyStore\InMemoryUrlKeyStore
 * @covers \LizardsAndPumpkins\DataPool\UrlKeyStore\IntegrationTestUrlKeyStoreAbstract
 */
class InMemoryUrlKeyStoreTest extends AbstractIntegrationTestUrlKeyStoreTest
{
    /**
     * @return UrlKeyStore
     */
    protected function createUrlKeyStoreInstance()
    {
        return new InMemoryUrlKeyStore();
    }
}
