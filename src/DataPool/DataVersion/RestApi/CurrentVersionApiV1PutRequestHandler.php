<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion\RestApi;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\DataVersion\RestApi\Exception\TargetDataVersionMissingInRequestException;
use LizardsAndPumpkins\DataPool\DataVersion\RestApi\Exception\UnableToDeserializeRequestBodyJsonException;
use LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommand;
use LizardsAndPumpkins\Http\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;

class CurrentVersionApiV1PutRequestHandler implements HttpRequestHandler
{
    const TARGET_VERSION_PARAM = 'current_version';

    /**
     * @var CommandQueue
     */
    private $commandQueue;

    public function __construct(CommandQueue $commandQueue)
    {
        $this->commandQueue = $commandQueue;
    }

    public function process(HttpRequest $request): HttpResponse
    {
        $versionString = $this->getTargetDataVersionFromRequest($request);
        $dataVersion = DataVersion::fromVersionString($versionString);
        $this->commandQueue->add(new SetCurrentDataVersionCommand($dataVersion));

        return GenericHttpResponse::create($body = '', $headers = [], HttpResponse::STATUS_ACCEPTED);
    }

    public function canProcess(HttpRequest $request): bool
    {
        return $request->getMethod() === HttpRequest::METHOD_PUT;
    }

    private function getTargetDataVersionFromRequest(HttpRequest $request): string
    {
        $data = $this->getRequestData($request);
        if (!is_array($data) || !isset($data[self::TARGET_VERSION_PARAM])) {
            throw new TargetDataVersionMissingInRequestException('The target data version is missing in the request body');
        }
        $versionString = $data[self::TARGET_VERSION_PARAM];

        return $versionString;
    }

    private function getRequestData(HttpRequest $request)
    {
        $data = json_decode($request->getRawBody(), true);
        if (json_last_error()) {
            $message = sprintf('Unable to deserialize request body JSON: %s', json_last_error_msg());
            throw new UnableToDeserializeRequestBodyJsonException($message);
        }

        return $data;
    }
}
