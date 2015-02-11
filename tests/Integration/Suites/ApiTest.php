<?php

namespace Brera\Tests\Integration;

use Brera\Environment\VersionedEnvironment;
use Brera\FrontendFactory;
use Brera\Http\HttpRequest;
use Brera\Http\HttpUrl;
use Brera\CommonFactory;
use Brera\IntegrationTestFactory;
use Brera\PoCMasterFactory;
use Brera\PoCWebFront;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnApiJsonResponse()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $request = HttpRequest::fromParameters('GET', $httpUrl);

        $website = new PoCWebFront($request);
        $website->registerFactory(new IntegrationTestFactory());
        $response = $website->runWithoutSendingResponse();

        $this->assertEquals('"dummy response"', $response->getBody());
    }
}
