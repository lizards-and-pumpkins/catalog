<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use League\CLImate\Argument\Manager as CliMateArgumentManager;
use League\CLImate\CLImate;
use LizardsAndPumpkins\AbstractIntegrationTest;
use LizardsAndPumpkins\ConsoleCommand\Command\ImportContentBlockConsoleCommand;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\TestFileFixtureTrait;

class ImportContentBlockConsoleCommandIntegrationTest extends AbstractIntegrationTest
{
    private $testRegularContentBlockContent = 'Dummy Regular Content Block Content';

    private $testListingContentBlockContent = 'Dummy Product Listing Content Block Content';

    use TestFileFixtureTrait;
    
    private function getCommandArgumentMap($overRideDefaults = []): array
    {
        $arguments = [
            'importDirectory' => ['importDirectory', $this->getUniqueTempDir()],
            'processQueues'   => ['processQueues', true],
            'help'            => ['help', null],
        ];
        foreach ($overRideDefaults as $name => $value) {
            $arguments[$name] = [$name, $value];
        }

        return array_values($arguments);
    }

    private function createTestCliMate(array $argumentMap): CLImate
    {
        /** @var CLImate|MockObject $stubCliMate */
        $stubCliMate = $this->createMock(CLImate::class);
        $stubCliMate->arguments = $this->createMock(CliMateArgumentManager::class);
        $stubCliMate->arguments->method('get')->willReturnMap($argumentMap);
        return $stubCliMate;
    }

    public function testRunImportsContentBlocksCommand(): void
    {
        $importDirectory = $this->getUniqueTempDir();
        $argumentMap = $this->getCommandArgumentMap(['importDirectory' => $importDirectory]);

        $listingContentBlockPath = $importDirectory . '/product_listing_content_block_sale.html';
        $regularContentBlockPath = $importDirectory . '/content_block_foo_bar.html'; 
        $this->createFixtureFile($listingContentBlockPath, $this->testListingContentBlockContent);
        $this->createFixtureFile($regularContentBlockPath, $this->testRegularContentBlockContent);
        
        $factory = $this->prepareIntegrationTestMasterFactory();
        $stubCLIMate = $this->createTestCliMate($argumentMap);
        $command = new ImportContentBlockConsoleCommand($factory, $stubCLIMate);
        $command->run();

        $this->failIfMessagesWhereLogged($factory->getLogger());
        
        /** @var SnippetKeyGenerator $keyGenerator */
        $keyGenerator = $factory->createContentBlockSnippetKeyGenerator('content_block_foo_bar');
        $context = $factory->createContextBuilder()->createContext([]);
        $dataPoolReader = $factory->createDataPoolReader();
        $regularContentBlockSnippet = $dataPoolReader->getSnippet($keyGenerator->getKeyForContext($context, []));
        $this->assertSame($this->testRegularContentBlockContent, $regularContentBlockSnippet);

        /** @var SnippetKeyGenerator $keyGenerator */
        $listingKeyGenerator = $factory->createProductListingContentBlockSnippetKeyGenerator('product_listing_content_block_');
        $key = $listingKeyGenerator->getKeyForContext($context, ['url_key' => 'sale']);
        $listingContentBlockSnippet = $dataPoolReader->getSnippet($key);
        $this->assertSame($this->testListingContentBlockContent, $listingContentBlockSnippet);
    }
}
