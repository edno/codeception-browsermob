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
     * @env blacklist
     * @env whitelist
     * @env limits
     * @env timeouts
     */
    public function parameters(FunctionalTester $I)
    {
        $port = $I->getProxyPort();
        $I->assertNotNull($port);
        $I->closeProxy();
        $port = $I->getProxyPort();
        //$I->assertNotNull($port); // BrowserMobProxy_Client issue
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
     * @covers ::openProxy
     * @covers ::startHar
     * @covers ::addPage
     * @covers ::getHar
     */
    public function captureHarWithPage(FunctionalTester $I)
    {
        $port = $I->openProxy();
        $I->assertNotNull($port, "`${port}` is not a valid port");
        $I->startHar('codeception');
        Requests::get('http://codeception.com/', [], ['proxy' => "127.0.0.1:${port}"]);
        $rep = $I->addPage('github');
        $I->assertTrue($rep);
        Requests::get('http://github.com/', [], ['proxy' => "127.0.0.1:${port}"]);
        $har = $I->getHar();
        $I->assertEquals('BrowserMob Proxy', $har['log']['creator']['name']);
        $I->assertNotEmpty($har['log']['entries']);
        $I->assertEquals('github', $har['log']['entries'][1]['pageref']);
        $I->closeProxy();
    }

    public function setHeaders(FunctionalTester $I)
    {
        $port = $I->openProxy();
        $I->assertNotNull($port, "`${port}` is not a valid port");
        $rep = $I->setHeaders(['User-Agent' => 'BrowserMob-Agent']);
        $I->assertTrue($rep);
        $I->startHar('codeception');
        Requests::get('http://codeception.com/', [], ['proxy' => "127.0.0.1:${port}"]);
        $I->getHar();
        $I->closeProxy();
    }

    public function redirectUrl(FunctionalTester $I)
    {
        $port = $I->openProxy();
        $I->assertNotNull($port, "`${port}` is not a valid port");
        $rep = $I->redirectUrl('http://testdomain.url/', 'http://codeception.com/');
        $I->assertTrue($rep);
        $I->startHar('codeception');
        Requests::get('http://testdomain.url/', [], ['proxy' => "127.0.0.1:${port}"]);
        $har = $I->getHar();
        $I->assertEquals('http://codeception.com/', $har['log']['entries'][0]['request']['url']);
        $I->closeProxy();
    }
}
