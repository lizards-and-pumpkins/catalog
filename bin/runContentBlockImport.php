#!/usr/bin/env php
<?php

namespace LizardsAndPumpkins;

use League\CLImate\CLImate;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockId;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand;
use LizardsAndPumpkins\Logging\LoggingCommandHandlerFactory;
use LizardsAndPumpkins\Logging\LoggingDomainEventHandlerFactory;
use LizardsAndPumpkins\Logging\LoggingQueueFactory;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\QueueMessageConsumer;
use LizardsAndPumpkins\Util\BaseCliCommand;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;
use LizardsAndPumpkins\Util\Factory\TwentyOneRunFactory;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../../autoload.php';
}

class RunContentBlockImport extends BaseCliCommand
{
    /**
     * @var MasterFactory
     */
    private $factory;

    private function __construct(MasterFactory $factory, CLImate $CLImate)
    {
        $this->factory = $factory;
        $this->setCLImate($CLImate);
    }

    /**
     * @return RunImport
     */
    public static function bootstrap()
    {
        $factory = new SampleMasterFactory();
        $commonFactory = new CommonFactory();
        $implementationFactory = new TwentyOneRunFactory();
        $factory->register($commonFactory);
        $factory->register($implementationFactory);
        //self::enableDebugLogging($factory, $implementationFactory, $commonFactory);

        return new self($factory, new CLImate());
    }

    private static function enableDebugLogging(
        MasterFactory $factory,
        TwentyOneRunFactory $implementationFactory,
        CommonFactory $commonFactory
    ) {
        $factory->register(new LoggingDomainEventHandlerFactory($commonFactory));
        $factory->register(new LoggingCommandHandlerFactory($commonFactory));
        $factory->register(new LoggingQueueFactory($implementationFactory));
    }

    /**
     * @param CLImate $climate
     * @return array[]
     */
    protected function getCommandLineArgumentsArray(CLImate $climate)
    {
        return array_merge(parent::getCommandLineArgumentsArray($climate), [
            'processQueues'   => [
                'prefix'      => 'p',
                'longPrefix'  => 'processQueues',
                'description' => 'Process queues after the import',
                'noValue'     => true,
            ],
            'importDirectory' => [
                'description' => 'Path to directory with import files',
                'required'    => true,
            ],
        ]);
    }

    protected function execute(CLImate $CLImate)
    {
        $this->addCommand();
        $this->processQueuesIfRequested();
    }

    private function addCommand()
    {
        $contentFileNames = glob($this->getArg('importDirectory') . '/*.html');

        array_map(function ($contentFileName) {
            $blockId = $this->createContentBlockIdBasedOnFileName($contentFileName);
            $blockContent = file_get_contents($contentFileName);
            $contextData = ['website' => 'fr', 'locale' => 'fr_FR'];
            $keyGeneratorParams = $this->createKeyGeneratorParamsBasedOnFileName($contentFileName);

            $contentBlockSource = new ContentBlockSource($blockId, $blockContent, $contextData, $keyGeneratorParams);

            $this->factory->getCommandQueue()->add(new UpdateContentBlockCommand($contentBlockSource));
        }, $contentFileNames);
    }

    /**
     * @param string $blockId
     * @return bool
     */
    private function isProductListingContentBlock($blockId)
    {
        return strpos($blockId, 'product_listing_content_block_') === 0;
    }

    /**
     * @param string $fileName
     * @return ContentBlockId
     */
    private function createContentBlockIdBasedOnFileName($fileName)
    {
        $blockIdString = preg_replace('/.*\/|\.html$/i', '', $fileName);

        if ($this->isProductListingContentBlock($blockIdString)) {
            $blockIdStringWithoutLastVariableToken = preg_replace('/_[^_]+$/', '', $blockIdString);
            return ContentBlockId::fromString($blockIdStringWithoutLastVariableToken);
        }

        return ContentBlockId::fromString($blockIdString);
    }

    private function createKeyGeneratorParamsBasedOnFileName($fileName)
    {
        $blockIdString = preg_replace('/.*\/|\.html$/i', '', $fileName);

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

    private function processCommandQueue()
    {
        $this->output('Processing command queue...');
        $this->processQueueWhileMessagesPending(
            $this->factory->getCommandQueue(),
            $this->factory->createCommandConsumer()
        );
    }

    private function processDomainEventQueue()
    {
        $this->output('Processing domain event queue...');
        $this->processQueueWhileMessagesPending(
            $this->factory->getEventQueue(),
            $this->factory->createDomainEventConsumer()
        );
    }

    private function processQueueWhileMessagesPending(Queue $queue, QueueMessageConsumer $consumer)
    {
        while ($queue->count()) {
            $consumer->process();
        }
    }
}

RunContentBlockImport::bootstrap()->run();
