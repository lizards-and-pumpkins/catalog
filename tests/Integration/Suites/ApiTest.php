<?php

namespace Brera\PoC\Tests\Integration;

use Brera\PoC\FrontendFactory;
use Brera\PoC\Http\HttpRequest;
use Brera\PoC\Http\HttpUrl;
use Brera\PoC\IntegrationTestFactory;
use Brera\PoC\PoCMasterFactory;
use Brera\PoC\PoCWebFront;

class ApiTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldReturnApiJsonResponse()
	{
		$httpUrl = HttpUrl::fromString('http://example.com/api/product/import');
		$request = HttpRequest::fromParameters('GET', $httpUrl);

		$factory = new PoCMasterFactory();
		$factory->register(new FrontendFactory());
		$factory->register(new IntegrationTestFactory());

		$website = new PoCWebFront($request, $factory);
		$response = $website->run(false);

		$this->assertEquals('"dummy response"', $response->getBody());
	}
} 
