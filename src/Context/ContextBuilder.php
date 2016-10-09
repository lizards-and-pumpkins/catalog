<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Http\HttpRequest;

interface ContextBuilder
{
    const REQUEST = 'request';
    
    public function createFromRequest(HttpRequest $request) : Context;

    /**
     * @param array[] $contextDataSets
     * @return Context[]
     */
    public function createContextsFromDataSets(array $contextDataSets) : array;

    /**
     * @param mixed[] $inputDataSet
     * @return Context
     */
    public function createContext(array $inputDataSet) : Context;

    /**
     * @param string[] $dataSet
     * @return Context
     */
    public static function rehydrateContext(array $dataSet) : Context;

    /**
     * @param Context $context
     * @param string[] $additionDataSet
     * @return Context
     */
    public function expandContext(Context $context, array $additionDataSet) : Context;
}
