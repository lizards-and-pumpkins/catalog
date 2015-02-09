<?php

namespace Brera\Tests\Integration;

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

		$factory = new PoCMasterFactory();
		$factory->register(new FrontendFactory());
		$factory->register(new CommonFactory());
		$factory->register(new IntegrationTestFactory());

		$website = new PoCWebFront($request, $factory);
		$response = $website->run(false);

		$this->assertEquals('"dummy response"', $response->getBody());
	}
} 
