<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\BaseCliCommand;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockId;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand;
use LizardsAndPumpkins\Messaging\Command\CommandConsumer;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Event\DomainEventConsumer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class ImportContentBlockConsoleCommand extends BaseCliCommand
{
    /**
     * @var MasterFactory
     */
    private $factory;

    public function __construct(MasterFactory $factory, CLImate $CLImate)
    {
        $this->factory = $factory;
        $this->setCLImate($CLImate);
    }

    /**
     * @param CLImate $climate
     * @return array[]
     */
    final protected function getCommandLineArgumentsArray(CLImate $climate): array
    {
        return array_merge(parent::getCommandLineArgumentsArray($climate), [
            'processQueues'   => [
                'prefix'      => 'p',
                'longPrefix'  => 'processQueues',
                'description' => 'Process queues after the import',
                'noValue'     => true,
            ],
            'importDirectory' => [
                'description' => 'Path to directory with *.html files to import',
                'required'    => true,
            ],
        ]);
    }

    final protected function execute(CLImate $CLImate)
    {
        $this->addUpdateContentBlockCommands();
        $this->processQueuesIfRequested();
    }

    private function addUpdateContentBlockCommands()
    {
        $contentFileNames = glob($this->getArg('importDirectory') . '/*.html');

        every($contentFileNames, function (string $contentFileName) {
            $blockId = $this->createContentBlockIdBasedOnFileName($contentFileName);
            $blockContent = file_get_contents($contentFileName);
            $keyGeneratorParams = $this->createKeyGeneratorParamsBasedOnFileName($contentFileName);
            
            $this->addCommandsForEachContext($blockId, $blockContent, $keyGeneratorParams);
        });
    }

    /**
     * @param ContentBlockId $blockId
     * @param string $blockContent
     * @param string[] $keyGeneratorParams
     */
    private function addCommandsForEachContext(ContentBlockId $blockId, string $blockContent, array $keyGeneratorParams)
    {
        every($this->getAllContexts(), function (Context $context) use ($blockId, $blockContent, $keyGeneratorParams) {
            $contentBlockSource = new ContentBlockSource($blockId, $blockContent, $context, $keyGeneratorParams);
            $this->getCommandQueue()->add(new UpdateContentBlockCommand($contentBlockSource));
        });
    }

    private function isProductListingContentBlock(string $blockId): bool
    {
        $prefix = ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy::LISTING_CONTENT_BLOCK_KEY_PREFIX;

        return strpos($blockId, $prefix) === 0;
    }

    private function createContentBlockIdBasedOnFileName(string $fileName): ContentBlockId
    {
        $blockIdString = $this->getContentBlockIdFromFileName($fileName);

        $this->validateContentBlockId($blockIdString);
        
        if ($this->isProductListingContentBlock($blockIdString)) {
            $blockIdStringWithoutLastVariableToken = preg_replace('/[^_]+$/', '', $blockIdString);

            return ContentBlockId::fromString($blockIdStringWithoutLastVariableToken);
        }

        return ContentBlockId::fromString($blockIdString);
    }

    /**
     * @param string $fileName
     * @return string[]
     */
    private function createKeyGeneratorParamsBasedOnFileName(string $fileName): array
    {
        $blockIdString = $this->getContentBlockIdFromFileName($fileName);

        if ($this->isProductListingContentBlock($blockIdString)) {
            $lastVariableTokenOfBlockId = preg_replace('/.*_/', '', $blockIdString);

            return ['url_key' => $lastVariableTokenOfBlockId];
        }

        return [];
    }

    private function processQueuesIfRequested()
    {
        if ($this->getArg('processQueues')) {
            $this->processQueues();
        }
    }

    private function processQueues()
    {
        $this->processCommandQueue();
        $this->processDomainEventQueue();
    }

    private function getContentBlockIdFromFileName(string $fileName): string
    {
        return preg_replace('/.*\/|\.html$/i', '', $fileName);
    }

    private function processCommandQueue()
    {
        $this->output('Processing command queue...');
        $this->createCommandConsumer()->processAll();
    }

    private function processDomainEventQueue()
    {
        $this->output('Processing domain event queue...');
        $this->createDomainEventConsumer()->processAll();
    }

    /**
     * @return Context[]
     */
    private function getAllContexts(): array
    {
        return $this->createContextSource()->getAllAvailableContextsWithVersionApplied($this->getDataVersion());
    }

    private function getDataVersion(): DataVersion
    {
        return DataVersion::fromVersionString($this->createDataPoolReader()->getCurrentDataVersion());
    }

    private function validateContentBlockId(string $blockId)
    {
        if (! preg_match('/^(:?product_listing_|)content_block_/', $blockId)) {
            $this->warn(sprintf('Warning: the content block "%s" is probably invalid.', $blockId));
            $this->warn('Content block IDs should start with "content_block_" or "product_listing_content_block_".');
        }
    }
    
    private function warn(string $message)
    {
        $this->getCLImate()->yellow($message);
    }

    private function getCommandQueue(): CommandQueue
    {
        return $this->factory->getCommandQueue();
    }

    private function createContextSource(): ContextSource
    {
        return $this->factory->createContextSource();
    }

    private function createDataPoolReader(): DataPoolReader
    {
        return $this->factory->createDataPoolReader();
    }

    private function createDomainEventConsumer(): DomainEventConsumer
    {
        return $this->factory->createDomainEventConsumer();
    }

    private function createCommandConsumer(): CommandConsumer
    {
        return $this->factory->createCommandConsumer();
    }
}
