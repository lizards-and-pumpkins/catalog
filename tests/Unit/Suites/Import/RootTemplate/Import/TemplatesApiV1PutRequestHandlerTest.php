<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\RootTemplate\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommand;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Http\HttpRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\Http\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommand
 */
class TemplatesApiV1PutRequestHandlerTest extends TestCase
{
    /**
     * @var CommandQueue|MockObject
     */
    private $mockCommandQueue;

    /**
     * @var TemplatesApiV1PutRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|MockObject
     */
    private $mockRequest;

    /**
     * @var DataVersion|MockObject
     */
    private $stubDataVersion;

    final protected function setUp(): void
    {
        $this->stubDataVersion = $this->createMock(DataVersion::class);
        $this->mockCommandQueue = $this->createMock(CommandQueue::class);
        $this->requestHandler = new TemplatesApiV1PutRequestHandler($this->mockCommandQueue, $this->stubDataVersion);

        $this->mockRequest = $this->createMock(HttpRequest::class);
    }

    public function testInheritsTheV2RequestHandler(): void
    {
        $this->assertInstanceOf(TemplatesApiV2PutRequestHandler::class, $this->requestHandler);
    }

    public function testEmitsUpdateTemplateCommandWithInjectedDataVersion(): void
    {
        $testContent = 'Raw Request Body';
        $this->stubDataVersion->method('__toString')->willReturn('foo');
        $this->mockRequest->method('getUrl')->willReturn(HttpUrl::fromString('http://example.com/api/templates/foo'));
        $this->mockRequest->method('getRawBody')->willReturn($testContent);

        $this->mockCommandQueue->expects($this->once())->method('add')
            ->willReturnCallback(function (UpdateTemplateCommand $command) use ($testContent) {
                $this->assertEquals((string) $this->stubDataVersion, $command->getDataVersion());
                $this->assertEquals($testContent, $command->getTemplateContent());
            });

        $this->requestHandler->process($this->mockRequest);
    }
}
