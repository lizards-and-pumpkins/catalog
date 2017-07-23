<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator;

/**
 * @covers \LizardsAndPumpkins\Import\RestApi\TemplateApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 */
class TemplateApiV1GetRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $expectedTemplateCodes = [
        'product_detail_view',
        'product_detail_meta',
    ];

    /**
     * @var TemplateApiV1GetRequestHandler
     */
    private $requestHandler;

    final protected function setUp()
    {
        /** @var TemplateProjectorLocator|\PHPUnit_Framework_MockObject_MockObject $stubTemplateProjectorLocator */
        $stubTemplateProjectorLocator = $this->createMock(TemplateProjectorLocator::class);
        $stubTemplateProjectorLocator->method('getRegisteredProjectorCodes')->willReturn($this->expectedTemplateCodes);
        $this->requestHandler = new TemplateApiV1GetRequestHandler($stubTemplateProjectorLocator);
    }

    public function testCanProcessGetRequest()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->createMock(HttpRequest::class);
        $stubHttpRequest->method('getMethod')->willReturn('GET');

        $this->assertTrue($this->requestHandler->canProcess($stubHttpRequest));
    }

    public function provideNonGetHttpVerbs()
    {
        return [
            ['PUT'],
            ['POST'],
            ['HEADER'],
            ['DELETE'],
        ];
    }

    /**
     * @dataProvider provideNonGetHttpVerbs
     */
    public function testCanProcessNonGetRequest(string $httpVerb)
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->createMock(HttpRequest::class);
        $stubHttpRequest->method('getMethod')->willReturn($httpVerb);

        $this->assertFalse($this->requestHandler->canProcess($stubHttpRequest));
    }

    public function testReturnsTemplateList()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->createMock(HttpRequest::class);
        $response = $this->requestHandler->process($stubHttpRequest);

        $this->assertEquals(['template_ids' => $this->expectedTemplateCodes], json_decode($response->getBody(), true));
    }
}
