<?php

declare(strict_types=1);

/**
 * @param array|\Traversable $items
 * @param callable $f
 */
function every($items, callable $f)
{
    foreach ($items as $index => $item) {
        $f($item, $index);
    }
}

/**
 * @param mixed $var
 * @return string
 */
function typeof($var) : string
{
    return is_object($var) ?
        get_class($var) :
        gettype($var);
}

function shutdown(int $exitCode = null)
{
    exit($exitCode);
}

/**
 * @experimental Please tell us if you use this function. Otherwise we might remove it again, should we not use it.
 */
function pipeline(callable $initialFunction, callable ...$otherFunctions): callable
{
    return array_reduce($otherFunctions, function (callable $previousFunctions, callable $nextFunction): callable {
        return function (...$args) use ($previousFunctions, $nextFunction) {
            return $nextFunction($previousFunctions(...$args));
        };
    }, $initialFunction);
}
