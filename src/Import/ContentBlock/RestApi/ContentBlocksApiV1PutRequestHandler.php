<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\ContentBlock\RestApi;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;

class ContentBlocksApiV1PutRequestHandler extends ContentBlocksApiV2PutRequestHandler
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    public function __construct(
        CommandQueue $commandQueue,
        ContextBuilder $contextBuilder,
        DataPoolReader $dataPoolReader
    ) {
        $this->dataPoolReader = $dataPoolReader;
        parent::__construct($commandQueue, $contextBuilder);
    }

    final protected function getDataVersion(array $requestBody): string
    {
        return $this->dataPoolReader->getCurrentDataVersion();
    }

}
