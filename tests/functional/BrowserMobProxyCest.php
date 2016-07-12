<?php

use Codeception\Extension\BrowserMob;
use Codeception\Util\Stub;

/**
 * @coversDefaultClass Codeception\Extension\BrowserMob
 */
class BrowserMobProxyCest
{
    public function initProxy(FunctionalTester $I)
    {
        $module = new BrowserMob(Stub::make('Codeception\Lib\ModuleContainer'));
        $I->assertInstanceOf('Codeception\Extension\BrowserMob', $module);
    }

    /**
     * @env autostart
     */
    public function parameterAutostart(FunctionalTester $I)
    {
        $port = $I->getProxyPort();
        $I->assertNotNull($port);
        $I->closeProxy();
        $port = $I->getProxyPort();
        $I->assertNotNull($port);
    }

    /**
     * @covers ::openProxy
     * @covers ::startHar
     * @covers ::getHar
     * @covers ::closeProxy
     */
    public function captureHar(FunctionalTester $I)
    {
        $port = $I->openProxy();
        $I->assertNotNull($port, "`${port}` is not a valid port");
        $rep = $I->startHar();
        $I->assertTrue($rep);
        Requests::get('http://codeception.com/', [], ['proxy' => "127.0.0.1:${port}"]);
        $har = $I->getHar();
        $I->assertEquals('BrowserMob Proxy', $har['log']['creator']['name']);
        $I->assertNotEmpty($har['log']['entries']);
        $I->assertEquals('http://codeception.com/', $har['log']['entries'][0]['request']['url']);
        $I->assertNotNull($har['log']['entries'][0]['serverIPAddress']);
        $I->closeProxy();
        $port = $I->getProxyPort();
        //$I->assertNull($port); // BrowserMobProxy_Client issue
    }

    /**
     * @env blacklist
     * @env whitelist
     */
    public function parameterBlackWhiteList(FunctionalTester $I)
    {
        $port = $I->getProxyPort();
        $I->assertNotNull($port);
        $I->closeProxy();
        $port = $I->getProxyPort();
        //$I->assertNotNull($port); // BrowserMobProxy_Client issue
    }

    /**
     * @env limits
     */
    public function parameterLimits(FunctionalTester $I)
    {
        $port = $I->getProxyPort();
        $I->assertNotNull($port);
        $I->closeProxy();
        $port = $I->getProxyPort();
        //$I->assertNotNull($port); // BrowserMobProxy_Client issue
    }

    /**
     * @env timeouts
     */
    public function parameterTimeouts(FunctionalTester $I)
    {
        $port = $I->getProxyPort();
        $I->assertNotNull($port);
        $I->closeProxy();
        $port = $I->getProxyPort();
        //$I->assertNotNull($port); // BrowserMobProxy_Client issue
    }
}
