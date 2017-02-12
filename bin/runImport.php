#!/usr/bin/env php
<?php

declare(strict_types = 1);

use LizardsAndPumpkins\ConsoleCommand\CliBootstrap;
use LizardsAndPumpkins\ConsoleCommand\Command\RunImport;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;

(function () {
    foreach ([__DIR__ . '/../../../autoload.php', __DIR__ . '/../vendor/autoload.php'] as $autoload) {
        if (file_exists($autoload)) {
            define('LP_COMPOSER_AUTOLOAD',  $autoload);
            break;
        }
    }
})();

if (! defined('LP_COMPOSER_AUTOLOAD')) {
    fwrite (STDERR, "Unable to find path to the composer vendor/autoload.php file.\n");
    exit(2);
}

require LP_COMPOSER_AUTOLOAD;

/** @var RunImport $command */
$factoriesToRegister = [new UpdatingProductImportCommandFactory(), new UpdatingProductListingImportCommandFactory()];
$command = CliBootstrap::create(RunImport::class, ...$factoriesToRegister);
$command->run();
