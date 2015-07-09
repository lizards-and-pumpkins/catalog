<?php

namespace Brera;

/**
 * @covers \Brera\DefaultHttpResponse
 */
class DefaultHttpResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultHttpResponse
     */
    private $defaultHttpResponse;

    public function setUp()
    {
        $this->defaultHttpResponse = new DefaultHttpResponse();
    }

    public function testBodyIsSetAndRetrieved()
    {
        $body = 'dummy';

        $this->defaultHttpResponse->setBody($body);
        $result = $this->defaultHttpResponse->getBody();

        $this->assertEquals($body, $result);
    }

    public function testBodyIsEchoed()
    {
        $body = 'dummy';

        $this->defaultHttpResponse->setBody($body);
        $this->defaultHttpResponse->send();

        $this->expectOutputString($body);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGivenHeaderIsIncludedIntoResponse()
    {
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped('This test requires the PHP extension xdebug to be installed.');
        }

        $customHeaderName = 'Foo';
        $customHeaderValue = 'bar';

        $this->defaultHttpResponse->addHeader($customHeaderName, $customHeaderValue);
        $this->defaultHttpResponse->send();

        $expectedHeader = $customHeaderName . ': ' . $customHeaderValue;
        $headers = xdebug_get_headers();

        $this->assertContains($expectedHeader, $headers);
    }
}
