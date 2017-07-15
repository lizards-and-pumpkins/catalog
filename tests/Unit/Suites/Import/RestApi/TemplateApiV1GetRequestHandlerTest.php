<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator;

class TemplateApiV1GetRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $expectedTemplates = [
        'product_detail_view',
        'product_detail_meta',
    ];

    /**
     * @var TemplateApiV1GetRequestHandler
     */
    private $handler;

    protected function setUp()
    {
        $templateProjectLocator = $this->createMock(TemplateProjectorLocator::class);
        $templateProjectLocator->method('getRegisteredProjectorCodes')->willReturn($this->expectedTemplates);
        $this->handler = new TemplateApiV1GetRequestHandler($templateProjectLocator);
    }

    public function testCanProcessGet()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $httpRequest */
        $httpRequest = $this->createMock(HttpRequest::class);
        $httpRequest->method('getMethod')->willReturn('GET');
        $this->assertTrue($this->handler->canProcess($httpRequest));
    }

    public function testCanNotProcessPut()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $httpRequest */
        $httpRequest = $this->createMock(HttpRequest::class);
        $httpRequest->method('getMethod')->willReturn('PUT');
        $this->assertFalse($this->handler->canProcess($httpRequest));
    }

    public function testProcessReturnsValidTemplateList()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $httpRequest */
        $httpRequest = $this->createMock(HttpRequest::class);
        $response = $this->handler->process($httpRequest);

        $this->assertEquals($this->expectedTemplates, json_decode($response->getBody()));
    }
}
