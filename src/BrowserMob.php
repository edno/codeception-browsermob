<?php
namespace Codeception\Extension;

use Codeception\Module;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;
use \PHPBrowserMobProxy_Client as BMP;
use \Requests;

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
        $host = $this->config['host'];
        if (isset($this->config['port'])) {
            $host = $host.':'.$this->config['port'];
        }

        // test if proxy is available
        if (static::__pingProxy($host)) {
            $this->bmp = new BMP($host);
        } else {
            throw new ModuleConfigException(__CLASS__, "Proxy '{$host}' cannot be reached");
        }

        // start a new BrowserMobProxy session
        $this->bmp->open();

        // set BrowserMobProxy options
        $this->__setProxyCapabilities();

    }

    protected static function __pingProxy($url)
    {
        try {
            $response = Requests::get('http://'.$url.'/proxy/');
        } catch(\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }

        return $response->success;
    }

    protected function __setProxyCapabilities()
    {
        $response = null;

        foreach ($this->config as $config => $data) {
            try {
                if (false === empty($data)) {
                    switch ($config) {
                        case 0: // fix a weird PHP behaviour: when $config === 0 then go in 'blacklist'
                            break;
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
                }
            } catch(\Exception $e) {
                throw new ModuleConfigException(__CLASS__, $e->getMessage());
            }

            if (get_class($response) === 'Request')
            {
                if (false === $response->success) {
                    throw new ModuleConfigException(__CLASS__, "Proxy response error '{$reponse->status_code}' {$respone->body}");
                }
            }
        }
    }

}
