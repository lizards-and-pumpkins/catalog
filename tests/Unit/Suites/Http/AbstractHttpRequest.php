<?php

namespace Brera\Http;

class AbstractHttpRequest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnAUrl()
    {
        $url = 'http://www.example.com/seo-url/';

        $stubHttpUrl = $this->getStubHttpUrl();
        $stubHttpUrl->expects($this->once())
        ->method('__toString')
        ->willReturn($url);

        $httpRequest = new HttpPostRequest($stubHttpUrl);
        $result = $httpRequest->getUrl();

        $this->assertEquals($result, $url);
    }

    /**
     * @test
     * @expectedException \Brera\Http\UnsupportedRequestMethodException
     * @expectedExceptionMessage Unsupported request method: "PUT"
     */
    public function itShouldThrowUnsupportedRequestMethodException()
    {
        $stubHttpUrl = $this->getStubHttpUrl();

        HttpRequest::fromParameters('PUT', $stubHttpUrl);
    }

    /**
     * @test
     */
    public function itShouldReturnAnHttpRequestFromAGlobalState()
    {
        $this->setUpGlobalState();

        $result = HttpRequest::fromGlobalState();

        $this->assertInstanceOf(HttpGetRequest::class, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnAnHttpRequestFromAGlobalStateOfASecureUrl()
    {
        $this->setUpGlobalState(true);

        $result = HttpRequest::fromGlobalState();

        $this->assertInstanceOf(HttpGetRequest::class, $result);
    }

    protected function getStubHttpUrl()
    {
        $stubHttpUrl = $this->getMockBuilder(HttpUrl::class)
        ->disableOriginalConstructor()
        ->getMock();

        return $stubHttpUrl;
    }

    private function setUpGlobalState($isSecure = false)
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTPS'] = $isSecure;
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/';
    }
}
