<?php

chdir(__DIR__ . '/../..');

require_once __DIR__ . '/../../vendor/autoload.php';

// Closure autoloader from
// https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md#closure-example

spl_autoload_register(function ($class) {
    $prefix = 'LizardsAndPumpkins\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);

    $files = [
        __DIR__ . '/Suites/' . str_replace('\\', '/', $relativeClass) . '.php',
        __DIR__ . '/Util/' . str_replace('\\', '/', $relativeClass) . '.php',
    ];

    foreach ($files as $file) {
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});
