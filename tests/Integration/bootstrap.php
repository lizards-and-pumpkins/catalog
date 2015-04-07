<?php

chdir(__DIR__ . '/../..');

require_once __DIR__ . '/../../vendor/autoload.php';

spl_autoload_register(function ($class) {
    $prefix = 'Brera\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);

    $file = __DIR__ . '/Suites/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
