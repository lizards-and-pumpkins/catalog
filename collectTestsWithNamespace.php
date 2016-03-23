<?php
$errorFile = 'refactorErrors.log';
file_put_contents($errorFile, '');

$classes = [];
foreach (csv2array('classes.csv') as $entry) {
    $classes[$entry['path']] = $entry;
}

$directory = new RecursiveDirectoryIterator('tests/Unit/Suites');
$iterator = new RecursiveIteratorIterator($directory);
$regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$allUnitTests = array_map(function ($elem) {
    return substr($elem[0], strlen('tests/Unit/Suites/'));
}, iterator_to_array($regex));

foreach ($allUnitTests as $unitTestFile) {
    $classPath = 'src/' . str_replace('Test.php', '.php', $unitTestFile);
    if (isset($classes[$classPath])) {
        $classes[$classPath]['test'] = 'tests/Unit/Suites/' . $unitTestFile;
    } else {

        file_put_contents(
            $errorFile,
            sprintf('No file found for "%s".', $unitTestFile) . "\n",
            FILE_APPEND
        );
    }
}

$csv = new SplFileObject('classesAndTests.csv', 'w');
$csv->fputcsv(array_keys(current($classes)));
foreach ($classes as $class) {
    $csv->fputcsv($class);
}

function csv2array($file)
{
    $array = $fields = [];
    $i = 0;
    $handle = fopen($file, "r");
    if ($handle) {
        while (($row = fgetcsv($handle, 4096, "\t", '"')) !== false) {
            if (empty($fields)) {
                $fields = $row;
                continue;
            }
            foreach ($row as $k => $value) {
                $array[$i][$fields[$k]] = $value;
            }
            $i++;
        }
        if (!feof($handle)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($handle);
    }
    return $array;
}
