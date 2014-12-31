<?php

namespace Brera\PoC\KeyValue;

interface KeyValueStore
{
    /**
     * @param string $key
     * @param mixed $value
     * @return null
     */
    public function set($key, $value);

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key
     * @return bool
     */
    public function has($key);
    
    // TODO: Implement multiSet and multiGet
} 
