<?php

namespace Brera\PoC;

class VersionedEnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VersionedEnvironment
     */
    private $environment;

    public function setUp()
    {
        $mockDataVersion = $this->getMockBuilder(DataVersion::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->environment = new VersionedEnvironment($mockDataVersion);
    }

    /**
     * @test
     */
    public function itShouldHaveAVersion()
    {
        $this->assertInstanceOf(
            DataVersion::class,
            $this->environment->getVersion()
        );
    }
}
