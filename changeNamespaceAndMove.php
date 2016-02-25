<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$classes  = file('classes.csv');
$replaces = [];
foreach ($classes as $class) {
    list($class, $path, $oldNamespace, $newNamespace) = str_getcsv($class);
    $replaces["$oldNamespace\\$class;"]  = "$newNamespace\\$class;";
    $replaces["$oldNamespace\\$class\n"] = "$newNamespace\\$class\n";
}

foreach ($classes as $class) {
    list($class, $path, $oldNamespace, $newNamespace) = str_getcsv($class);
    $content = file_get_contents($path);

    // change every absolute path to another changed class
    $content = str_replace(array_keys($replaces), array_values($replaces), $content);

    // change namespace
    $content = str_replace("namespace $oldNamespace", "namespace $newNamespace", $content);

    file_put_contents($path, $content);
}

foreach ($classes as $class) {
    list($class, $oldPath, $oldNamespace, $newNamespace) = str_getcsv($class);

    $namespaces = explode('\\', $newNamespace);

    // remove LizardsAndPumpkins
    array_shift($namespaces);
    $newPath         = implode('/', $namespaces);
    $pathDirectories = [
        'tests/Unit/Suites/',
        'src',
        'tests/Integration/Suites/',
    ];
    $start           = '';
    foreach ($pathDirectories as $start) {
        if (strpos($oldPath, $start) === 0) {
            break;
        }
    }

    rename($oldPath, $start . $newPath . $class . '.php');
}
