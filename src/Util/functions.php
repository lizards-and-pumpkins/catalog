<?php

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
function typeof($var)
{
    return is_object($var) ?
        get_class($var) :
        gettype($var);
}
