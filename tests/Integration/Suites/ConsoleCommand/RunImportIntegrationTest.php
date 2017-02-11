<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use League\CLImate\Argument\Manager as CliMateArgumentManager;
use League\CLImate\CLImate;
use LizardsAndPumpkins\AbstractIntegrationTest;
use LizardsAndPumpkins\ConsoleCommand\Command\RunImport;
use LizardsAndPumpkins\Import\XPathParser;

class RunImportIntegrationTest extends AbstractIntegrationTest
{
    private $fixtureFile = __DIR__ . '/../../shared-fixture/simple_product_adilette.xml';

    private function getCommandArgumentMap($overRideDefaults = []): array
    {
        $arguments = [
            'clearStorage'      => ['clearStorage', false],
            'importImages'      => ['importImages', false],
            'importFile'        => ['importFile', $this->fixtureFile],
            'processQueues'     => ['processQueues', true],
            'environmentConfig' => ['environmentConfig', ''],
            'help'              => ['help', null],
        ];
        foreach ($overRideDefaults as $name => $value) {
            $arguments[$name] = [$name, $value];
        }
        return array_values($arguments);
    }

    private function getSkuOfFirstSimpleProductInFixture(string $fixtureFile) : string
    {
        $xml = file_get_contents($fixtureFile);
        $parser = new XPathParser($xml);
        $skuNode = $parser->getXmlNodesArrayByXPath('//catalog/products/product[@type="simple"][1]/@sku');
        return $skuNode[0]['value'];
    }

    private function getProductJsonSnippetForId(string $productIdString) : string
    {
        $key = $this->getProductJsonSnippetKeyForId($productIdString);

        return $this->getSnippetFromDataPool($key);
    }

    private function getSnippetFromDataPool(string $key) : string
    {
        return $this->factory->createDataPoolReader()->getSnippet($key);
    }

    private function getProductJsonSnippetKeyForId(string $productIdString) : string
    {
        $keyGenerator = $this->factory->createProductJsonSnippetKeyGenerator();
        $context = $this->factory->createContextBuilder()->createContext([]);
        return $keyGenerator->getKeyForContext($context, ['product_id' => $productIdString]);
    }

    private function createTestCliMate(array $argumentMap): CLImate
    {
        /** @var CLImate|\PHPUnit_Framework_MockObject_MockObject $stubCliMate */
        $stubCliMate = $this->createMock(CLImate::class);
        $stubCliMate->arguments = $this->createMock(CliMateArgumentManager::class);
        $stubCliMate->arguments->method('get')->willReturnMap($argumentMap);
        return $stubCliMate;
    }
    
    public function testRunImportsCommand()
    {
        
        
        $factory = $this->prepareIntegrationTestMasterFactory();
        $command = new RunImport($factory, $this->createTestCliMate($this->getCommandArgumentMap()));
        $command->run();
        
        $sku = $this->getSkuOfFirstSimpleProductInFixture($this->fixtureFile);

        $simpleProductSnippet = $this->getProductJsonSnippetForId($simpleProductIdString);

        $simpleProductData = json_decode($simpleProductSnippet, true);
        $this->assertEquals($simpleProductIdString, $simpleProductData['product_id']);
        $this->assertEquals('simple', $simpleProductData['type_code']);
        
        
    }
}
