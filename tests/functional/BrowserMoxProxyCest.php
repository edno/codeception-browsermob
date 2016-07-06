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
}
