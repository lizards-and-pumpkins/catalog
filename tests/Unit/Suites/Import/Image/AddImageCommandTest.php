<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Import\Image\Exception\ImageFileDoesNotExistException;
use LizardsAndPumpkins\Import\Image\Exception\NoAddImageCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\TestFileFixtureTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Image\AddImageCommand
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class AddImageCommandTest extends TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var string
     */
    private $imageFilePath;

    /**
     * @var DataVersion|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDataVersion;

    /**
     * @var AddImageCommand
     */
    private $command;

    protected function setUp()
    {
        $fixtureDirectoryPath = $this->getUniqueTempDir();
        $this->imageFilePath = $fixtureDirectoryPath . '/foo.png';
        $this->createFixtureDirectory($fixtureDirectoryPath);
        $this->createFixtureFile($this->imageFilePath, '');

        $this->stubDataVersion = $this->createMock(DataVersion::class);
        $this->command = new AddImageCommand($this->imageFilePath, $this->stubDataVersion);
    }

    public function testCommandInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testImageFileNameIsReturned()
    {
        $result = $this->command->getImageFilePath();
        $this->assertSame($this->imageFilePath, $result);
    }

    public function testItThrowsAnExceptionIfTheImageDoesNotExist()
    {
        $this->expectException(ImageFileDoesNotExistException::class);
        $this->expectExceptionMessage('The image file does not exist: "foo.png"');
        new AddImageCommand('foo.png', $this->stubDataVersion);
    }

    public function testItReturnsTheInjectedDataVersion()
    {
        $this->assertSame($this->stubDataVersion, $this->command->getDataVersion());
    }

    public function testReturnsMessageWithCommandCodeName()
    {
        $message = $this->command->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(AddImageCommand::CODE, $message->getName());
    }

    public function testReturnsMessageWithExpectedPayload()
    {
        $this->stubDataVersion->method('__toString')->willReturn('123');
        $expectedPayload = ['file_path' => $this->imageFilePath, 'data_version' => '123'];
        $message = $this->command->toMessage();
        $this->assertSame($expectedPayload, $message->getPayload());
    }

    public function testCanBeRehydratedFromMessage()
    {
        $this->stubDataVersion->method('__toString')->willReturn('123');
        $message = $this->command->toMessage();
        $rehydratedCommand = AddImageCommand::fromMessage($message);
        $this->assertInstanceOf(AddImageCommand::class, $rehydratedCommand);

        $this->assertSame($this->command->getImageFilePath(), $rehydratedCommand->getImageFilePath());
        $this->assertSame((string)$this->command->getDataVersion(), (string)$rehydratedCommand->getDataVersion());
    }

    public function testThrowsExceptionIfTheSourceMessageNameIsNotAddImage()
    {
        $this->expectException(NoAddImageCommandMessageException::class);
        $this->expectExceptionMessage('Unable to rehydrate from "foo" queue message, expected "add_image"');

        $message = Message::withCurrentTime('foo', [], []);
        AddImageCommand::fromMessage($message);
    }
}
