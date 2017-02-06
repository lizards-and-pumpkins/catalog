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
function pipeline(callable $f, callable ...$fs): callable
{
    return array_reduce($fs, function (callable $acc, callable $f): callable {
        return function (...$args) use ($acc, $f) {
            return $f($acc(...$args));
        };
    }, $f);
}
