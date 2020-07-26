<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Core\Factory\MasterFactory;

class TestRestApiWebFront extends CatalogRestApiWebFront
{
    /**
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $testMasterFactory;

    public function __construct(
        HttpRequest $request,
        MasterFactory $testMasterFactory,
        UnitTestFactory $unitTestFactory
    ) {
        parent::__construct($request, $unitTestFactory);
        $this->testMasterFactory = $testMasterFactory;
    }

    final protected function createMasterFactory() : MasterFactory
    {
        return $this->testMasterFactory;
    }
}
