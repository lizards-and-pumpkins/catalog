<?php
namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Http\HttpRequest;

interface ContextBuilder
{
    /**
     * @param HttpRequest $request
     * @return Context
     */
    public function createFromRequest(HttpRequest $request);

    /**
     * @param array[] $contextDataSets
     * @return Context[]
     */
    public function createContextsFromDataSets(array $contextDataSets);

    /**
     * @param mixed[] $contextDataSet
     * @return Context
     */
    public function createContext(array $contextDataSet);

    /**
     * @param string[] $dataSet
     * @return Context
     */
    public static function rehydrateContext(array $dataSet);
}
