<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion\RestApi;

use LizardsAndPumpkins\DataPool\DataVersion\RestApi\Exception\TargetDataVersionMissingException;
use LizardsAndPumpkins\DataPool\DataVersion\RestApi\Exception\UnableToDeserializeRequestBodyJsonException;
use LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommand;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpPostRequest
 * @uses   \LizardsAndPumpkins\Http\HttpRequest
 * @uses   \LizardsAndPumpkins\Http\HttpGetRequest
 * @uses   \LizardsAndPumpkins\Http\HttpPutRequest
 * @uses   \LizardsAndPumpkins\Http\HttpRequestBody
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommand
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandler
 */
class CurrentVersionApiV1PutRequestHandlerTest extends TestCase
{
    /**
     * @var CommandQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandQueue;

    private function createHandler(): CurrentVersionApiV1PutRequestHandler
    {
        return new CurrentVersionApiV1PutRequestHandler($this->mockCommandQueue);
    }

    private function createPutRequestForVersion(string $dataVersion): HttpRequest
    {
        return $this->createHttpRequest(HttpRequest::METHOD_PUT, $dataVersion);
    }

    private function createHttpRequest(string $requestMethod, string $dataVersion): HttpRequest
    {
        return HttpRequest::fromParameters(
            $requestMethod,
            HttpUrl::fromString('https://example.com/api/current_version'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody(json_encode(['current_version' => $dataVersion]))
        );
    }

    protected function setUp()
    {
        $this->mockCommandQueue = $this->createMock(CommandQueue::class);
    }

    public function testInheritsFromApiRequestHandler()
    {
        $this->assertInstanceOf(ApiRequestHandler::class, $this->createHandler());
    }

    /**
     * @dataProvider nonPutHttpRequestMethodProvider
     */
    public function testDoesNotHandleNonPutRequests(string $nonPutRequestMethod)
    {
        $request = $this->createHttpRequest($nonPutRequestMethod, '');
        $this->assertFalse($this->createHandler()->canProcess($request));
    }

    public function nonPutHttpRequestMethodProvider(): array
    {
        return [
            'post' => [HttpRequest::METHOD_POST],
            'get'  => [HttpRequest::METHOD_GET],
        ];
    }

    public function testHandlesPutRequests()
    {
        $request = $this->createPutRequestForVersion('foo');
        $this->assertTrue($this->createHandler()->canProcess($request));
    }

    /**
     * @dataProvider requestDataWithoutTargetVersionDataProvider
     */
    public function testThrowsExceptionIfTargetDataVersionIsMissing($missingTargetVersionRequestData)
    {
        $this->expectException(TargetDataVersionMissingException::class);
        $this->expectExceptionMessage('The target data version is missing in the request body');
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_PUT,
            HttpUrl::fromString('https://example.com/api/current_version'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody(json_encode($missingTargetVersionRequestData))
        );
        $this->createHandler()->process($request);
    }

    public function requestDataWithoutTargetVersionDataProvider(): array
    {
        return [
            'empty array'          => [[]],
            'string'               => ['foo'],
            'null'                 => [null],
            'array with other key' => ['previous_version' => 'baz'],
        ];
    }

    public function testThrowsExceptionIfRequestBodyIsNotJson()
    {
        $this->expectException(UnableToDeserializeRequestBodyJsonException::class);
        $this->expectExceptionMessage('Unable to deserialize request body JSON: ');
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_PUT,
            HttpUrl::fromString('https://example.com/api/current_version'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('???')
        );
        $this->createHandler()->process($request);
    }

    public function testAddsSetCurrentDataVersionCommandToCommandQueue()
    {
        $dataVersionString = 'bat';

        $this->mockCommandQueue->expects($this->once())->method('add')
            ->willReturnCallback(function (SetCurrentDataVersionCommand $command) use ($dataVersionString) {
                $this->assertEquals($dataVersionString, $command->getDataVersion());
            });

        $request = $this->createPutRequestForVersion($dataVersionString);
        $this->createHandler()->process($request);
    }

    public function testReturnsResponseWithAcceptedHttpStatusCode()
    {
        $request = $this->createPutRequestForVersion('foo');
        $response = $this->createHandler()->process($request);
        $this->assertSame(HttpResponse::STATUS_ACCEPTED, $response->getStatusCode());
    }
}
