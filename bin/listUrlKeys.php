#!/usr/bin/env php
<?php

namespace LizardsAndPumpkins;

use League\CLImate\CLImate;
use LizardsAndPumpkins\DataPool\DataPoolReader;

require_once __DIR__ . '/../vendor/autoload.php';

class ListUrlKeys
{
    const IDX_URL_KEY = 0;
    const IDX_CONTEXT = 1;
    
    /**
     * @var MasterFactory
     */
    private $factory;

    /**
     * @var CLImate
     */
    private $climate;

    private function __construct(MasterFactory $factory, CLImate $CLImate)
    {
        $this->factory = $factory;
        $this->climate = $CLImate;
    }

    /**
     * @return ListUrlKeys
     */
    public static function bootstrap()
    {
        $factory = new SampleMasterFactory();
        $factory->register(new CommonFactory());
        $factory->register(new SampleFactory());

        return new self($factory, new CLImate());
    }

    public function run()
    {
        try {
            $this->prepareCommandLineArguments();
            $this->execute();
        } catch (\Exception $e) {
            $this->climate->error($e->getMessage());
            $this->climate->error(sprintf('%s:%d', $e->getFile(), $e->getLine()));
            $this->climate->usage();
        }
    }

    private function prepareCommandLineArguments()
    {
        $this->climate->arguments->add([
            'withContext' => [
                'prefix' => 'c',
                'longPrefix' => 'withContext',
                'description' => 'Display the context string together with the URL keys',
                'noValue' => true
            ],
            'dataVersion' => [
                'description' => 'List url keys for the given catalog data version',
                'defaultValue' => 'current',
                'required' => false
            ]
        ]);

        $this->validateArguments();
    }

    private function validateArguments()
    {
        $this->climate->arguments->parse();
    }

    private function execute()
    {
        $version = $this->getVersionToDisplay();
        $rawUrlKeyRecords = $this->getDataPoolReader()->getUrlKeysForVersion($version);
        $formattedUrlKeys = $this->getFormattedUrlKeysArray($rawUrlKeyRecords);
        $this->outputArray($formattedUrlKeys);
    }

    /**
     * @param array[] $rawUrlKeyRecords
     * @return string[]
     */
    private function getFormattedUrlKeysArray($rawUrlKeyRecords)
    {
        return $this->getArg('withContext') ?
            $this->formatUrlKeysWithContext($rawUrlKeyRecords) :
            $this->formatUrlKeysWithoutContext($rawUrlKeyRecords);
    }

    /**
     * @param array[] $rawUrlKeyRecords
     * @return string[]
     */
    private function formatUrlKeysWithoutContext(array $rawUrlKeyRecords)
    {
        $this->outputMessage('URL keys without context (without duplicates):');
        return array_unique(array_map(function (array $urlKeyRecord) {
            return $urlKeyRecord[self::IDX_URL_KEY];
        }, $rawUrlKeyRecords));
    }

    /**
     * @param array $rawUrlKeyRecords
     * @return string[]
     */
    private function formatUrlKeysWithContext(array $rawUrlKeyRecords)
    {
        $this->outputMessage('URL keys with context:');
        return array_unique(array_map(function (array $urlKeyRecord) {
            return sprintf("%-30s\t%s", $urlKeyRecord[self::IDX_URL_KEY], $urlKeyRecord[self::IDX_CONTEXT]);
        }, $rawUrlKeyRecords));
    }

    /**
     * @param string $arg
     * @return bool|float|int|null|string
     */
    private function getArg($arg)
    {
        return $this->climate->arguments->get($arg);
    }

    /**
     * @param string $message
     */
    private function output($message)
    {
        $this->climate->output($message);
    }

    /**
     * @param string $message
     */
    private function outputMessage($message)
    {
        $this->climate->bold($message);
    }

    /**
     * @return bool|float|int|null|string
     */
    private function getVersionToDisplay()
    {
        $version = $this->getArg('dataVersion') === 'current' ?
            $this->getDataPoolReader()->getCurrentDataVersion() :
            $this->getArg('dataVersion');
        return $version;
    }

    /**
     * @return DataPoolReader
     */
    private function getDataPoolReader()
    {
        return $this->factory->createDataPoolReader();
    }

    /**
     * @param string[] $formattedUrlKeys
     */
    private function outputArray(array $formattedUrlKeys)
    {
        array_map(function ($urlKey) {
            $this->output($urlKey);
        }, $formattedUrlKeys);
    }
}

ListUrlKeys::bootstrap()->run();
