<?php

namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

/**
 * @covers \LizardsAndPumpkins\DataPool\UrlKeyStore\InMemoryUrlKeyStore
 * @covers \LizardsAndPumpkins\DataPool\UrlKeyStore\IntegrationTestUrlKeyStoreAbstract
 */
class InMemoryUrlKeyStoreTest extends AbstractIntegrationTestUrlKeyStoreTest
{
    final protected function createUrlKeyStoreInstance() : UrlKeyStore
    {
        return new InMemoryUrlKeyStore();
    }
}
