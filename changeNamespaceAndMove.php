<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$classes = file('classes.csv');
$replaces = [];
array_shift($classes); // remove headline
foreach ($classes as $class) {
    list($newNamespace, $class, $oldPath, $oldNamespace) = str_getcsv($class, "\t");
    $newNamespace = 'LizardsAndPumpkins\\' . $newNamespace;
    $replaces["$oldNamespace\\$class;"] = "$newNamespace\\$class;";
    $replaces["$oldNamespace\\$class\n"] = "$newNamespace\\$class\n";
}

foreach ($classes as $class) {
    list($newNamespace, $class, $oldPath, $oldNamespace) = str_getcsv($class, "\t");
    $newNamespace = 'LizardsAndPumpkins\\' . $newNamespace;
    $content = file_get_contents($oldPath);

    // change every absolute path to another changed class
    $content = str_replace(array_keys($replaces), array_values($replaces), $content);

    // change namespace
    $content = str_replace("namespace $oldNamespace", "namespace $newNamespace", $content);

    file_put_contents($oldPath, $content);
}

foreach ($classes as $class) {
    list($newNamespace, $class, $oldPath, $oldNamespace) = str_getcsv($class, "\t");
    $newNamespace = 'LizardsAndPumpkins\\' . $newNamespace;

    $namespaces = explode('\\', $newNamespace);

    // remove LizardsAndPumpkins
    array_shift($namespaces);
    $newPath = implode('/', $namespaces);
    $pathDirectories = [
        'tests/Unit/Suites/',
        'src',
        'tests/Integration/Suites/',
    ];
    $start = '';
    foreach ($pathDirectories as $start) {
        if (strpos($oldPath, $start) === 0) {
            break;
        }
    }

    rename($oldPath, $start . '/' . $newPath . '/' . $class . '.php');
}
