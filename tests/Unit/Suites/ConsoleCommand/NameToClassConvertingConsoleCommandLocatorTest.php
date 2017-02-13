<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use LizardsAndPumpkins\ConsoleCommand\Command\TestStubConsoleCommand;
use LizardsAndPumpkins\ConsoleCommand\Exception\InvalidConsoleCommandNameException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\NameToClassConvertingConsoleCommandLocator
 */
class NameToClassConvertingConsoleCommandLocatorTest extends TestCase
{
    private function createLocator(): NameToClassConvertingConsoleCommandLocator
    {
        return new NameToClassConvertingConsoleCommandLocator();
    }

    public function testImplementsConsoleCommandLocatorInterface()
    {
        $this->assertInstanceOf(ConsoleCommandLocator::class, $this->createLocator());
    }

    public function testThrowsExceptionIfTheCommandNameIsEmpty()
    {
        $this->expectException(InvalidConsoleCommandNameException::class);
        $this->expectExceptionMessage('The command name must not be an empty string');
        
        $this->createLocator()->hasClassForName('');
    }

    /**
     * @dataProvider commandNameWithInvalidCharactersProvider
     */
    public function testThrowsExceptionIfTheConsoleCommandNameContainsInvalidCharacters(string $invalidName)
    {
        $this->expectException(InvalidConsoleCommandNameException::class);
        $this->expectExceptionMessage(sprintf('The command name "%s" is invalid', $invalidName));
        
        $this->createLocator()->hasClassForName($invalidName);
    }

    public function commandNameWithInvalidCharactersProvider(): array
    {
        return [
            'contains number' => ['some0command'],
            'contains backslash' => ['some\\command'],
            'contains slash' => ['some/command'],
        ];
    }
    
    public function testReturnsFalseIfTheNameCanNotBeMappedToAnExistingClass()
    {
        $this->assertFalse($this->createLocator()->hasClassForName('foo'));
    }
    
    public function testReturnsTrueIfTheNameCanBeMappedToAnExistingClass()
    {
        $this->assertTrue($this->createLocator()->hasClassForName('test:stub'));
    }

    public function testThrowsAnExceptionOnGetClassWhenTheClassNameIsInvalid()
    {
        $this->expectException(InvalidConsoleCommandNameException::class);
        $this->expectExceptionMessage('The command name "foo bar" is invalid');

        $this->createLocator()->getClassFromName('foo bar');
    }

    public function testThrowsExceptionIfTheClassDoesNotExist()
    {
        $this->expectException(InvalidConsoleCommandNameException::class);
        $this->expectExceptionMessage('The command "foo" is unknown');
        $this->createLocator()->getClassFromName('foo');
    }

    public function testReturnsClassNameOfExistingCommand()
    {
        $this->assertSame('\\' . TestStubConsoleCommand::class, $this->createLocator()->getClassFromName('test:stub'));
    }
}
