<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use League\CLImate\Argument\Manager as CliMateArgumentManager;
use League\CLImate\CLImate;
use LizardsAndPumpkins\AbstractIntegrationTest;
use LizardsAndPumpkins\ConsoleCommand\Command\ImportTemplateConsoleCommand;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class ImportTemplateConsoleCommandIntegrationTest extends AbstractIntegrationTest
{
    private function getCommandArgumentMap($overRideDefaults = []): array
    {
        $arguments = [
            'processQueues' => ['processQueues', true],
            'list'          => ['list', false],
            'templateId'    => ['templateId', 'product_listing'],
            'help'          => ['help', null],
        ];
        foreach ($overRideDefaults as $name => $value) {
            $arguments[$name] = [$name, $value];
        }
        return array_values($arguments);
    }

    private function getProductListingPageTemplateSnippet(MasterFactory $factory): string
    {
        /** @var DataPoolReader $dataPoolReader */
        $dataPoolReader = $factory->createDataPoolReader();

        return $dataPoolReader->getSnippet($this->getProductListingPageTemplateSnippetKey($factory));
    }

    private function getProductListingPageTemplateSnippetKey(MasterFactory $factory): string
    {
        /** @var SnippetKeyGenerator $keyGenerator */
        $keyGenerator = $factory->createProductListingTemplateSnippetKeyGenerator();
        $context = $factory->createContextBuilder()->createContext([]);

        return $keyGenerator->getKeyForContext($context, []);
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
        $command = new ImportTemplateConsoleCommand($factory, $this->createTestCliMate($this->getCommandArgumentMap()));
        $command->run();
        
        $this->failIfMessagesWhereLogged($factory->getLogger());
        $snippet = $this->getProductListingPageTemplateSnippet($factory);

        $this->assertContains('{{snippet product_listing_content_block_top}}', $snippet);
    }
}
