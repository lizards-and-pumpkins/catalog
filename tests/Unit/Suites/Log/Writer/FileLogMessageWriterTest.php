<?php


namespace Brera\Log\Writer;

use Brera\Log\LogMessage;
use Brera\TestFileFixtureTrait;

/**
 * @covers \Brera\Log\Writer\FileLogMessageWriter
 */
class FileLogMessageWriterTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var FileLogMessageWriter
     */
    private $persister;

    /**
     * @var string
     */
    private $testLogFilePath;

    /**
     * @var LogMessage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubLogMessage;

    private function removeTestLogFileIfExists()
    {
        if (file_exists($this->testLogFilePath)) {
            if (!is_writable($this->testLogFilePath)) {
            }
            unlink($this->testLogFilePath);
        }
    }

    private function removeTestLogDirIfExists()
    {
        $logDir = dirname($this->testLogFilePath);
        if (file_exists($logDir)) {
            rmdir($logDir);
        }
    }

    private function ensureDirectoryAndFileCanBeCleanedUp()
    {
        $logDirPath = dirname($this->testLogFilePath);
        if (file_exists($logDirPath) && !is_writable($logDirPath)) {
            chmod($logDirPath, 0700);
        }
        if (file_exists($this->testLogFilePath) && !is_writable($this->testLogFilePath)) {
            chmod($this->testLogFilePath, 0500);
        }
    }

    protected function setUp()
    {
        $logDir = $this->getUniqueTempDir();
        $this->createFixtureDirectory($logDir);
        $this->testLogFilePath = $logDir . '/dir/file.log';
        $this->persister = new FileLogMessageWriter($this->testLogFilePath);
        $this->stubLogMessage = $this->getMock(LogMessage::class);
        $this->stubLogMessage->method('__toString')->willReturn('test log message');
    }

    protected function tearDown()
    {
        $this->ensureDirectoryAndFileCanBeCleanedUp();
        $this->removeTestLogFileIfExists();
        $this->removeTestLogDirIfExists();
        parent::tearDown();
    }

    public function testItIsALogMessagePersister()
    {
        $this->assertInstanceOf(LogMessageWriter::class, $this->persister);
    }

    public function testItCreatesTheLogFileDirectory()
    {
        $this->persister->persist($this->stubLogMessage);
        $this->assertFileExists(dirname($this->testLogFilePath));
    }

    public function testItThrowsAnExceptionIfTheLogDirectoryCanNotBeCreated()
    {
        $this->setExpectedException(UnableToCreateLogDirectoryException::class);
        $persister = new FileLogMessageWriter('');
        $persister->persist($this->stubLogMessage);
    }

    public function testItThrowsAnExceptionIfTheLogDirectoryIsNotWritable()
    {
        $this->setExpectedException(
            UnableToCreateLogFileException::class,
            sprintf('The log directory is not writable: "%s"', dirname($this->testLogFilePath))
        );
        $logDirectoryPath = dirname($this->testLogFilePath);
        $this->createFixtureDirectory($logDirectoryPath);
        chmod($logDirectoryPath, 0000);
        $this->persister->persist($this->stubLogMessage);
    }

    public function testItThrowsAnExceptionIfTheLogFileIsNotWritable()
    {
        $this->setExpectedException(
            UnableToWriteToLogFileException::class,
            sprintf('The log file is not writable: "%s"', $this->testLogFilePath)
        );
        $this->createFixtureFile($this->testLogFilePath, '', 0400);
        $this->persister->persist($this->stubLogMessage);
    }

    public function testItWritesMessagesToTheLogFile()
    {
        $this->stubLogMessage->method('getContext')->willReturn(['a' => new \stdClass, 'b' => [PHP_EOL]]);
        
        $this->persister->persist($this->stubLogMessage);
        $content = file_get_contents($this->testLogFilePath);
        
        $message = 'The log file did not contain the log message content';
        $this->assertContains((string)$this->stubLogMessage, $content, $message);
        
        // ISO 8601 Example: 2015-09-03T18:45:52+02:00
        $iso8601pattern = '/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d\+\d\d:\d\d/';
        $this->assertRegExp($iso8601pattern, $content);

        $expected = 'Array ( [a] => stdClass Object ( ) [b] => Array ( [0] => ) )';
        $this->assertContains($expected, $content);
        
        $this->assertContains(get_class($this->stubLogMessage), $content);
    }

    public function testItAppendsToExistingContent()
    {
        $existingContent = "already existing content\n";
        $this->createFixtureFile($this->testLogFilePath, $existingContent, 0600);
        $this->persister->persist($this->stubLogMessage);
        $this->assertContains($existingContent, file_get_contents($this->testLogFilePath));
    }
}
