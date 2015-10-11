#!/usr/bin/env php
<?php


namespace LizardsAndPumpkins;

use League\CLImate\CLImate;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Utils\BaseCliCommand;

require_once __DIR__ . '/../vendor/autoload.php';

class ListUrlKeys extends BaseCliCommand
{
    const IDX_URL_KEY = 0;
    const IDX_CONTEXT = 1;
    const IDX_TYPE = 2;

    /**
     * @var MasterFactory
     */
    private $factory;

    private function __construct(MasterFactory $factory, CLImate $CLImate)
    {
        $this->factory = $factory;
        $this->setCLImate($CLImate);
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

    /**
     * @param CLImate $CLImate
     * @return array[]
     */
    protected function getCommandLineArgumentsArray(CLImate $CLImate)
    {
        return array_merge(parent::getCommandLineArgumentsArray($CLImate), [
            'withContext' => [
                'prefix' => 'c',
                'longPrefix' => 'withContext',
                'description' => 'Display the context string together with the URL keys',
                'noValue' => true
            ],
            'type' => [
                'prefix' => 't',
                'longPrefix' => 'type',
                'description' => 'Display url keys for page type only (listing or product or all)',
                'required' => false,
                'defaultValue' => 'all'
            ],
            'dataVersion' => [
                'description' => 'List url keys for the given catalog data version',
                'defaultValue' => 'current',
                'required' => false
            ]
        ]);
    }

    protected function execute(CLImate $climate)
    {
        $version = $this->getVersionToDisplay();
        $type = $this->getArg('type');
        $rawUrlKeyRecords = $this->getDataPoolReader()->getUrlKeysForVersion($version);
        $urlKeyRecordsForType = array_filter($rawUrlKeyRecords, function ($rawUrlKeyRecord) use ($type) {
            return $type === 'all' || $rawUrlKeyRecord[self::IDX_TYPE] === $type;
        });
        $formattedUrlKeys = $this->getFormattedUrlKeysArray($urlKeyRecordsForType);
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
        $this->outputMessage('URL keys without context:');
        return array_unique(array_map(function (array $urlKeyRecord) {
            return $urlKeyRecord[self::IDX_URL_KEY];
        }, $rawUrlKeyRecords));
    }

    /**
     * @param string[] $rawUrlKeyRecords
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
     * @param string $message
     */
    private function outputMessage($message)
    {
        $this->getCLImate()->bold($message);
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
