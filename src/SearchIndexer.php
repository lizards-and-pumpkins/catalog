<?php

namespace Brera;

use Brera\Environment\EnvironmentSource;
use Brera\Product\ProductSource;

interface SearchIndexer
{
    /**
     * @param ProductSource $productSource
     * @param EnvironmentSource $environmentSource
     * @return void
     */
    public function index(ProductSource $productSource, EnvironmentSource $environmentSource);
}
