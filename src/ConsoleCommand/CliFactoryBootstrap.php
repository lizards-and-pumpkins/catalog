<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ConsoleCommand;

use LizardsAndPumpkins\Core\Factory\Factory;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\ProjectFactory;

class CliFactoryBootstrap
{
    protected static $projectFactoryClass = ProjectFactory::class;

    protected static $commonFactoryClass = CommonFactory::class;

    public static function createMasterFactory(Factory ...$factoriesToRegister): MasterFactory
    {
        $masterFactory = new CatalogMasterFactory();

        self::registerDefaultFactories($masterFactory, $factoriesToRegister);
        self::registerSpecifiedFactories($masterFactory, $factoriesToRegister);

        return $masterFactory;
    }

    /**
     * @param MasterFactory $masterFactory
     * @param Factory[] $otherFactories
     */
    private static function registerDefaultFactories(MasterFactory $masterFactory, array $otherFactories): void
    {
        every(self::getDefaultFactoryClasses(), function (string $class) use ($masterFactory, $otherFactories) {
            if (class_exists($class) && ! self::arrayContainsInstanceOfClass($otherFactories, $class)) {
                $masterFactory->register(new $class);
            }
        });
    }

    /**
     * @param MasterFactory $masterFactory
     * @param Factory[] $factoriesToRegister
     */
    private static function registerSpecifiedFactories(MasterFactory $masterFactory, array $factoriesToRegister): void
    {
        every($factoriesToRegister, function (Factory $factory) use ($masterFactory) {
            $masterFactory->register($factory);
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

    /**
     * @param mixed[] $objects
     * @param string $class
     * @return bool
     */
    private static function arrayContainsInstanceOfClass(array $objects, string $class): bool
    {
        foreach ($objects as $object) {
            if ($object instanceof $class) {
                return true;
            }
        }

        return false;
    }
}
