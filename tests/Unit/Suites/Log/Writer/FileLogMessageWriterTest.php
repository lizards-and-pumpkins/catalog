<?php


namespace LizardsAndPumpkins\Log\Writer;

use LizardsAndPumpkins\Log\LogMessage;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Log\Writer\FileLogMessageWriter
 */
class FileLogMessageWriterTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var FileLogMessageWriter
     */
    private $writer;

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
        $this->writer = new FileLogMessageWriter($this->testLogFilePath);
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

    public function testItIsALogMessageWriter()
    {
        $this->assertInstanceOf(LogMessageWriter::class, $this->writer);
    }

    public function testItCreatesTheLogFileDirectory()
    {
        $this->stubLogMessage->method('getContext')->willReturn([]);
        $this->writer->write($this->stubLogMessage);
        $this->assertFileExists(dirname($this->testLogFilePath));
    }

    public function testItThrowsAnExceptionIfTheLogDirectoryCanNotBeCreated()
    {
        $this->setExpectedException(UnableToCreateLogDirectoryException::class);
        $writer = new FileLogMessageWriter('');
        $writer->write($this->stubLogMessage);
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
        $this->writer->write($this->stubLogMessage);
    }

    public function testItThrowsAnExceptionIfTheLogFileIsNotWritable()
    {
        $this->setExpectedException(
            UnableToWriteToLogFileException::class,
            sprintf('The log file is not writable: "%s"', $this->testLogFilePath)
        );
        $this->createFixtureFile($this->testLogFilePath, '', 0400);
        $this->writer->write($this->stubLogMessage);
    }

    public function testItWritesMessagesToTheLogFile()
    {
        $this->stubLogMessage->method('getContext')->willReturn([
            'a' => new \stdClass,
            'b' => [1, 2, 3],
            'c' => "string\n",
            'd' => true,
            'e' => new \RuntimeException
        ]);

        $this->writer->write($this->stubLogMessage);
        $content = file_get_contents($this->testLogFilePath);

        $message = 'The log file did not contain the log message content';
        $this->assertContains((string)$this->stubLogMessage, $content, $message);

        // ISO 8601 Example: 2015-09-03T18:45:52+02:00
        $iso8601pattern = '/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d\+\d\d:\d\d/';
        $this->assertRegExp($iso8601pattern, $content);

        $expected = 'Array ( [0] => a => stdClass [1] => b => Array(3) [2] => c => (string) string [3] => d => boolean [4] => e => RuntimeException';
        $this->assertContains($expected, $content);

        $this->assertContains(get_class($this->stubLogMessage), $content);
    }

    public function testItAppendsToExistingContent()
    {
        $this->stubLogMessage->method('getContext')->willReturn([]);
        $existingContent = "already existing content\n";
        $this->createFixtureFile($this->testLogFilePath, $existingContent, 0600);
        $this->writer->write($this->stubLogMessage);
        $this->assertContains($existingContent, file_get_contents($this->testLogFilePath));
    }
}
