<?php


namespace Brera\PoC;


interface KeyValueStore
{
    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value);

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param $key
     * @return bool
     */
    public function has($key);
    
    // TODO: multiSet und multiGet
} 