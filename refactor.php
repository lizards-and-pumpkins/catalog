<?php

$classes = array_filter(explode("\n", `grep --exclude "vendor/" -r '^class ' *`));
$classMetaInformation = [];

$fh = fopen('classes.csv', 'w');
fputcsv($fh, ['class', 'path', 'old-namespace', 'new-namespace']);
foreach ($classes as $line) {
    $line = trim(explode('extends', $line)[0]);
    $line = trim(explode('implements', $line)[0]);
    if (!preg_match('#(.*):class (.*)#', $line, $matches)) {
        continue;
    }

    list(, $path, $className) = $matches;
    $classMetaInformation = [
        'class' => $className,
        'path' => $path,
        'namespace' => findNamespace($path),
    ];
    #var_dump($classMetaInformation);
    fputcsv($fh, $classMetaInformation);
}

fclose($fh);

function findNamespace($file)
{
    $fh = fopen($file, 'r');
    while (($buffer = fgets($fh)) !== false) {
        if (strpos($buffer, 'namespace') !== false) {
            return substr(substr(trim($buffer), 0, -1), 10);
        }
    }

    return $file;
}
