<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\BaseCliCommand;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

use function array_filter as filter;
use function array_map as map;
use function array_merge as merge;
use function array_unique as unique;

class ReportUrlKeysConsoleCommand extends BaseCliCommand
{
    const IDX_URL_KEY = 0;
    const IDX_CONTEXT = 1;
    const IDX_TYPE = 2;
    
    const TYPE_ALL = 'all';
    const TYPE_LISTING = 'listing';
    const TYPE_PRODUCT = 'product';
    
    const CURRENT_VERSION = 'current';

    /**
     * @var MasterFactory
     */
    private $factory;

    public function __construct(MasterFactory $factory, CLImate $CLImate)
    {
        $this->factory = $factory;
        $this->setCLImate($CLImate);
    }

    /**
     * @param CLImate $CLImate
     * @return array[]
     */
    final protected function getCommandLineArgumentsArray(CLImate $CLImate): array
    {
        return merge(
            parent::getCommandLineArgumentsArray($CLImate),
            [
                'type'        => [
                    'prefix'       => 't',
                    'longPrefix'   => 'type',
                    'description'  => 'Display url keys for page type only ("listing" or "product" or "all")',
                    'required'     => false,
                    'defaultValue' => self::TYPE_ALL,
                ],
                'dataVersion' => [
                    'description'  => 'List url keys for the given catalog data version',
                    'defaultValue' => self::CURRENT_VERSION,
                    'required'     => false,
                ],
            ]
        );
    }

    final protected function execute(CLImate $climate)
    {
        $version = $this->getDataVersionToDisplay();
        $urlKeys = $this->getUrlKeys($version, $this->getArg('type'));
        $formattedUrlKeys = $this->formatUrlKeys($urlKeys);
        
        $this->outputMessage('URL keys:');
        $this->outputArray($formattedUrlKeys);
    }

    /**
     * @param string $version
     * @param string $type
     * @return array[]
     */
    private function getUrlKeys(string $version, string $type): array
    {
        $allUrlKeyRecords = $this->getDataPoolReader()->getUrlKeysForVersion($version);

        return self::TYPE_ALL === $type ?
            $allUrlKeyRecords :
            $this->filterUrlKeyRecordsByType($allUrlKeyRecords, $type);
    }

    /**
     * @param array[] $urlKeyRecords
     * @param string $type
     * @return array[]
     */
    private function filterUrlKeyRecordsByType(array $urlKeyRecords, string $type): array
    {
        return filter($urlKeyRecords, function (array $rawUrlKeyRecord) use ($type) {
            return $rawUrlKeyRecord[self::IDX_TYPE] === $type;
        });
    }

    /**
     * @param array[] $rawUrlKeyRecords
     * @return string[]
     */
    private function formatUrlKeys(array $rawUrlKeyRecords): array
    {
        return unique(map(function (array $urlKeyRecord) {
            return $urlKeyRecord[self::IDX_URL_KEY];
        }, $rawUrlKeyRecords));
    }

    private function outputMessage(string $message)
    {
        $this->getCLImate()->bold($message);
    }

    private function getDataVersionToDisplay(): string
    {
        $version = $this->getArg('dataVersion') === self::CURRENT_VERSION ?
            $this->getDataPoolReader()->getCurrentDataVersion() :
            $this->getArg('dataVersion');

        return (string) $version;
    }

    private function getDataPoolReader(): DataPoolReader
    {
        return $this->factory->createDataPoolReader();
    }

    /**
     * @param string[] $formattedUrlKeys
     */
    private function outputArray(array $formattedUrlKeys)
    {
        every($formattedUrlKeys, function ($urlKey) {
            $this->output($urlKey);
        });
    }
}
