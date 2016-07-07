<?php
namespace Codeception\Extension;

use Codeception\Module;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;
use \PHPBrowserMobProxy_Client as BMP;
use \Requests;
use \RuntimeException;

class BrowserMob extends Module
{

    protected $config = ['host', 'port', 'autostart', 'blacklist', 'whitelist', 'limits', 'timeouts', 'redirect', 'retry', 'basicAuth'];

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
        if (isset($this->config['autostart'])) {
            if (true === (bool)$this->config['autostart']) {
                $this->openProxy();
            }
        }
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

    protected function __setProxyCapabilities($capabilities)
    {
        $response = null;

        foreach ($capabilities as $config => $data) {
            try {
                if (false === empty($data)) {
                    switch ($config) {
                        case 0: // fix a weird PHP behaviour: when $config === 0 then go in 'blacklist'
                            break;
                        case 'blacklist':
                            $response = $this-_blacklist($data);
                            break;
                        case 'whitelist':
                            $response = $this->_whitelist($data);
                            break;
                        case 'limits':
                            $response = $this->_limits($data);
                            break;
                        case 'timeouts':
                            $response = $this->_timeouts($data);
                            break;
                        case 'redirect':
                            $response = $this->_remapHosts($data);
                            break;
                        case 'retry':
                            $response = $this->_retry($data);
                            break;
                        case 'basicAuth':
                            $response = $this->_basicAuth($data);
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

    public function openProxy($capabilities = null)
    {
        try {
            $this->bmp->open();
            if (empty($capabilities)) {
                $capabilities = $this->config;
            }
            $this->__setProxyCapabilities($capabilities);
        } catch(\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    public function closeProxy()
    {
        try {
            $this->bmp->close();
        } catch(\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    public function startHar()
    {
        try {
            $response = $this->bmp->newHar();
        } catch(\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
        return $response->success;
    }

    public function startPage()
    {
        try {
            $response = $this->bmp->newPage();
        } catch(\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
        return $response->success;
    }

    public function getHar()
    {
        try {
            return $this->bmp->har;
        } catch(\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    public function getLimit()
    {
        try {
            return $this->bmp->getLimit();
        } catch(\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    // magic function giving direct access to BrowserMobProxy class methods
    public function __call($method, $args)
    {
        // check if is a command call
        if (preg_match('/^_[A-z]+$/', $method)) {
            // extract standard method name
            $method = preg_filter('/_/', '', $method);
            // set call array for calling method
            $call = array($this, $method);
            // check if method is callable
            if (is_callable($call)) {
                call_user_func_array($call, $args);
            } else {
                throw new RuntimeException("Method ${method} does not exist or is not callable");
            }
        } else {
            throw new RuntimeException("Method ${method} does not exist or is not callable");
        }
    }

}
