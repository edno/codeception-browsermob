<?php

use Codeception\Extension\BrowserMob;
use Codeception\Util\Stub;

class BrowserMobProxyCest
{
    public function initProxy(FunctionalTester $I)
    {
        $module = new BrowserMob(Stub::make('Codeception\Lib\ModuleContainer'));
        $I->assertInstanceOf('Codeception\Extension\BrowserMob', $module);
    }

    public function getHar(FunctionalTester $I)
    {
        $I->openProxy();
        $I->startHar();
        Requests::get('http://www.github.com', [], ['proxy' => '127.0.0.1:9090']);
        $I->assertNotNull($I->getHar());

    }
}
