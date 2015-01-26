<?php

namespace Brera\Renderer;

use Brera\ProjectionSourceData;

/**
 * @covers \Brera\Renderer\Block
 */
class BlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Block
     */
    private $block;

    /**
     * @var Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubLayout;

    protected function setUp()
    {
        $this->stubLayout = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stubDataObject = $this->getMock(ProjectionSourceData::class);

        $this->block = new Block($this->stubLayout, $stubDataObject);
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\TemplateFileNotReadableException
     */
    public function itShouldThrowAnExceptionIfTemplateFileDoesNotExist()
    {
        $this->block->render();
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\TemplateFileNotReadableException
     */
    public function itShouldThrowAnExceptionIfTemplateFileIsNotReadable()
    {
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'some-file-name.xml';

        touch($filePath);
        chmod($filePath, 000);

        $this->stubLayout->expects($this->once())
            ->method('getAttribute')
            ->with('template')
            ->willReturn($filePath);

        $this->block->render();
    }
}
