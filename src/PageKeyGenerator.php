<?php


namespace Brera;


use Brera\Http\HttpUrl;

class PageKeyGenerator
{

    /**
     * @todo logic needs to be implemented and overthink
     * @todo we need somehow to define which information goes into this first key
     * @todo I like the idea to only add the version and everything else can be
     * @todo done in this first snippet, so it can be just "empty" only containing one palceholder
     *
     * @param HttpUrl     $url
     * @param Environment $env
     *
     * @return string
     */
    public function getKeyForSnippet(HttpUrl $url, Environment $env)
    {
        // $path contains the starting /
        return $this->getKey($url, $env);
    }

    /**
     * @param HttpUrl     $url
     * @param Environment $env
     *
     * @return string
     */
    public function getKeyForSnippetList(HttpUrl $url, Environment $env)
    {
        return $this->getKey($url, $env) . '_l';
    }

    /**
     * @param             $url
     * @param Environment $env
     *
     * @return mixed|string
     */
    private function getKey($url, Environment $env)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $key = preg_replace('#[^a-zA-Z0-9]#', '_', $path);
        $key .= '_' . $env->getVersion();
        return $key;
    }
}
