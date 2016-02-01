<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\KeyValue\Credis\CredisKeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\SearchEngine\Solr\SolrSearchEngine;

class ProductionDataPoolFactory implements Factory
{
    use FactoryTrait;

    /**
     * @return SearchEngine
     */
    public function createSearchEngine()
    {
        $solrConnectionPath = 'http://localhost:8983/solr/gettingstarted/';
        $searchableAttributes = $this->getMasterFactory()->getSearchableAttributeCodes();
        return new SolrSearchEngine($solrConnectionPath, $searchableAttributes);
    }

    /**
     * @return CredisKeyValueStore
     */
    public function createKeyValueStore()
    {
        $client = new \Credis_Client();
        return new CredisKeyValueStore($client);
    }
}
