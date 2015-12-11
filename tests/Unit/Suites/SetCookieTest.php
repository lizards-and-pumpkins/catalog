<?php

namespace LizardsAndPumpkins;

class SetCookieTest extends \PHPUnit_Framework_TestCase
{
    private static $setCookieValues = [];

    public static function trackSetCookieCalls($name, $value, $expire)
    {
        self::$setCookieValues[] = [$name, $value, $expire];
    }

    private function getCookies()
    {
        return self::$setCookieValues;
    }

    protected function tearDown()
    {
        self::$setCookieValues = [];
    }

    public function testCookieSetting()
    {
        (new SetsCookies())->doSomething();
        $this->assertContains(['foo', 'bar', 3600], $this->getCookies());
        $this->assertContains(['bar', 'baz', 9000], $this->getCookies());
    }
}

class SetsCookies
{
    public function doSomething()
    {
        setcookie('foo', 'bar', 3600);
        setcookie('bar', 'baz', 9000);
    }
}

function setcookie($name, $value, $expire)
{
    SetCookieTest::trackSetCookieCalls($name, $value, $expire);
}
