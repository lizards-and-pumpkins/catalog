<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use League\CLImate\Argument\Manager as CliMateArgumentManager;
use League\CLImate\CLImate;
use LizardsAndPumpkins\AbstractIntegrationTest;
use LizardsAndPumpkins\CatalogFixtureFileQuery;
use LizardsAndPumpkins\ConsoleCommand\Command\ImportCatalogConsoleCommand;
use LizardsAndPumpkins\Import\Image\NullProductImageImportCommandFactory;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\TestDataPoolQuery;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;

class ImportCatalogConsoleCommandIntegrationTest extends AbstractIntegrationTest
{
    private $fixtureFile = 'simple_product_adilette.xml';

    private function getCommandArgumentMap($overRideDefaults = []): array
    {
        $fixtureFile = CatalogFixtureFileQuery::getPathToFixtureFile($this->fixtureFile);
        $arguments = [
            'clearStorage'      => ['clearStorage', false],
            'importImages'      => ['importImages', false],
            'importFile'        => ['importFile', $fixtureFile],
            'processQueues'     => ['processQueues', true],
            'environmentConfig' => ['environmentConfig', ''],
            'help'              => ['help', null],
        ];
        foreach ($overRideDefaults as $name => $value) {
            $arguments[$name] = [$name, $value];
        }
        return array_values($arguments);
    }

    private function createTestCliMate(array $argumentMap): CLImate
    {
        /** @var CLImate|\PHPUnit_Framework_MockObject_MockObject $stubCliMate */
        $stubCliMate = $this->getMockBuilder(CLImate::class)->setMethods(['get', 'output', 'error'])->getMock();
        $stubCliMate->arguments = $this->createMock(CliMateArgumentManager::class);
        $stubCliMate->arguments->method('get')->willReturnMap($argumentMap);
        $stubCliMate->expects($this->any())->method('error')->with('');
        return $stubCliMate;
    }

    /**
     * @return CatalogMasterFactory
     */
    private function createMasterFactory(): CatalogMasterFactory
    {
        $factoriesToExclude = [
            new UpdatingProductImportCommandFactory(),
            new UpdatingProductListingImportCommandFactory(),
            new NullProductImageImportCommandFactory(),
        ];

        return $this->prepareIntegrationTestMasterFactoryExcludingFactories($factoriesToExclude);
    }

    public function testRunImportsCatalogCommand()
    {
        $factory = $this->createMasterFactory();
        
        $command = new ImportCatalogConsoleCommand($factory, $this->createTestCliMate($this->getCommandArgumentMap()));
        $command->run();

        $simpleProductIdString = CatalogFixtureFileQuery::getSkuOfFirstSimpleProductInFixture($this->fixtureFile);

        $simpleProductSnippet = TestDataPoolQuery::getProductJsonSnippetForId($factory, $simpleProductIdString);

        $simpleProductData = json_decode($simpleProductSnippet, true);
        $this->assertEquals($simpleProductIdString, $simpleProductData['product_id']);
        $this->assertEquals('simple', $simpleProductData['type_code']);
    }
}
