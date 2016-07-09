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
        $port = $I->openProxy();
        $I->startHar();
        Requests::get('http://codeception.com/', [], ['proxy' => "127.0.0.1:${port}"]);
        $har = $I->getHar();
        $I->assertEquals('BrowserMob Proxy', $har['log']['creator']['name']);
        $I->assertEquals('http://codeception.com/', $har['log']['entries'][0]['request']['url']);
        $I->assertNotNull($har['log']['entries'][0]['serverIPAddress']);
    }
}
