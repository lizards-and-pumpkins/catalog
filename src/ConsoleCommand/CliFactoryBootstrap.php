<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\ProjectFactory;

class CliFactoryBootstrap
{
    protected static $projectFactoryClass = ProjectFactory::class;
    protected static $commonFactoryClass = CommonFactory::class;
    
    public static function createFactory(Factory ...$factoriesToRegister): MasterFactory
    {
        $masterFactory = new \LizardsAndPumpkins\Util\Factory\SampleMasterFactory();
        
        self::registerDefaultFactories($masterFactory, $factoriesToRegister);
        self::registerSpecifiedFactories($masterFactory, $factoriesToRegister);

        return $masterFactory;
    }

    /**
     * @param MasterFactory $masterFactory
     * @param Factory[] $otherFactories
     */
    private static function registerDefaultFactories(MasterFactory $masterFactory, array $otherFactories)
    {
        every(self::getDefaultFactoryClasses(), function (string $class) use ($masterFactory, $otherFactories) {
            if (class_exists($class) && !self::arrayContainsInstanceOfClass($otherFactories, $class)) {
                $masterFactory->register(new $class);
            }
        });
    }

    /**
     * @return string[]
     */
    private static function getDefaultFactoryClasses(): array
    {
        return [
            static::$commonFactoryClass,
            static::$projectFactoryClass,
        ];
    }

    private static function arrayContainsInstanceOfClass(array $objects, string $class): bool
    {
        foreach ($objects as $object) {
            if ($object instanceof $class) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param MasterFactory $masterFactory
     * @param Factory[] $factoriesToRegister
     */
    private static function registerSpecifiedFactories(MasterFactory $masterFactory, array $factoriesToRegister)
    {
        every($factoriesToRegister, function (Factory $factory) use ($masterFactory) {
            $masterFactory->register($factory);
        });
    }
}
