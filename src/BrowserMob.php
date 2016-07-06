<?php
namespace Codeception\Extension;

use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;
use \PHPBrowserMobProxy_Client as BMP;

class BrowserMob extends Module
{

    protected $config = ['host', 'port', 'blacklist', 'whitelist', 'limits', 'timeouts', 'redirect', 'retry'];

    protected $requiredFields = ['host'];

    protected $lastResponse;

    private $bmp;

    /**
     * @codeCoverageIgnore
     * @ignore Codeception specific
     */
    public function _initialize()
    {
        $url = $this->config['url'];
        if (isset($this->config['port'])) {
            $url = $url.':'.$this->config['port'];
        }

        // test if proxy is available
        if (static::__pingProxy($url)) {
            $this->bmp = new BMP($url);
        } else {
            throw new ModuleConfigException(get_class($this), "Proxy '{$url}' cannot be reached");
        }

        // start a new BrowserMobProxy session
        $this->bmp->open();

        // set BrowserMobProxy options
        $this->__setProxyCapabilities();

    }

    protected static function __pingProxy($url)
    {
        try {
            $response = Requests::get($url);
        } catch(\Exception $e) {
            throw new ModuleException(get_class($this), $e->getMessage());
        }

        return $response->success;
    }

    protected function __setProxyCapabilities()
    {
        foreach ($this->config as $config => $data) {
            try {
                switch ($config) {
                    case 'blacklist':
                        $response = $this->bmp->blacklist($data);
                        break;
                    case 'whitelist':
                        $response = $this->bmp->whitelist($data);
                        break;
                    case 'limits':
                        $response = $this->bmp->limits($data);
                        break;
                    case 'timeouts':
                        $response = $this->bmp->timeouts($data);
                        break;
                    case 'redirect':
                        $response = $this->bmp->remapHosts($data);
                        break;
                    case 'retry':
                        $response = $this->bmp->retry($data);
                        break;
                    default:
                        // do nothing
                }
            } catch(\Exception $e) {
                throw new ModuleConfigException(get_class($this), $e->getMessage());
            }
            if (false === $response->success) {
                throw new ModuleConfigException(get_class($this), "Proxy response error '{$reponse->status_code}' {$respone->body}");
            }
        }
    }

}
