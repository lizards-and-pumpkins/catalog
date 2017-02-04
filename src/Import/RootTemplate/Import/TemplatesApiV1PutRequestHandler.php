<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RootTemplate\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommand;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use LizardsAndPumpkins\Http\HttpRequest;

class TemplatesApiV1PutRequestHandler extends TemplatesApiV2PutRequestHandler
{
    /**
     * @var DataVersion
     */
    private $dataVersion;

    public function __construct(CommandQueue $commandQueue, DataVersion $dataVersion)
    {
        parent::__construct($commandQueue);
        $this->dataVersion = $dataVersion;
    }

    final protected function getDataVersion(HttpRequest $request): DataVersion
    {
        return $this->dataVersion;
    }

    final protected function getContent(HttpRequest $request): string
    {
        return $request->getRawBody();
    }

}
