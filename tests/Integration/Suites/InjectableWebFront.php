<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Core\Factory\Factory;
use LizardsAndPumpkins\Core\Factory\MasterFactory;

class InjectableWebFront extends CatalogWebFront
{
    /**
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $testMasterFactory;
    
    public function __construct(HttpRequest $request, MasterFactory $testMasterFactory, Factory $implementationFactory)
    {
        parent::__construct($request, $implementationFactory);
        $this->testMasterFactory = $testMasterFactory;
    }

    final protected function createMasterFactory() : MasterFactory
    {
        return $this->testMasterFactory;
    }

    final protected function registerFactories(MasterFactory $masterFactory): void
    {
        // All factories must already have been registered with the injected testing master factory.
    }
}
