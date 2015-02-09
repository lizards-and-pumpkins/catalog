<?php

namespace Brera\Renderer;

use Brera\ProjectionSourceData;

/**
 * @covers \Brera\Renderer\Block
 */
class BlockTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'some-file-name.phtml';

        if (file_exists($filePath) && is_file($filePath)) {
            chmod($filePath, '600');
            unlink($filePath);
        }
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\TemplateFileNotReadableException
     */
    public function itShouldThrowAnExceptionIfTemplateFileDoesNotExist()
    {
        $stubDataObject = $this->getStubProjectionSourceData();
        $block = new Block('foo.phtml', $stubDataObject);
        $block->render();
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\TemplateFileNotReadableException
     */
    public function itShouldThrowAnExceptionIfTemplateFileIsNotReadable()
    {
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'some-file-name.phtml';

        touch($filePath);
        chmod($filePath, 000);

        $stubDataObject = $this->getStubProjectionSourceData();

        $block = new Block($filePath, $stubDataObject);
        $block->render();
    }

    /**
     * @test
     */
    public function itShouldReturnSameString()
    {
        $stubDataObject = $this->getStubProjectionSourceData();
        $block = new Block('foo.phtml', $stubDataObject);
        $result = $block->__('foo');

        $this->assertEquals('foo', $result);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ProjectionSourceData
     */
    private function getStubProjectionSourceData()
    {
        return $this->getMock(ProjectionSourceData::class);
    }
}
