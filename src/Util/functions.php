<?php

/**
 * @param array|\Traversable $items
 * @param callable $f
 */
function every($items, callable $f)
{
    foreach ($items as $index => $item) {
        call_user_func($f, $item, $index);
    }
}
