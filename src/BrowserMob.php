<?php
namespace Codeception\Extension;

use Codeception\Module;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;
use \PHPBrowserMobProxy_Client as BMP;
use \Requests;
use \RuntimeException;

/**
 * @method void _open()
 * @method void _close()
 * @method string _newHar(string $label='')
 * @method string _newPage(string $label='')
 * @method string _blacklist(string $regexp, integer $status_code)
 * @method string _whitelist(string $regexp, integer $status_code)
 * @method string _basicAuth(string $domain, string[] $options)
 * @method string _headers(string[] $options)
 * @method string _responseInterceptor(string $js)
 * @method string _requestInterceptor(string $js)
 * @method Requests_Response _limits(string[] $options)
 * @method Requests_Response _timeouts(string[] $options)
 * @method string _remapHosts(string $address, string $ip_address)
 * @method string _waitForTrafficToStop(integer $quiet_period, integer $timeout)
 * @method string _clearDnsCache()
 * @method string _rewriteUrl(string $match, string $replace)
 * @method string _retry(integer $retry_count)
 */
class BrowserMob extends Module
{

    protected $config = ['host', 'port', 'autostart', 'blacklist', 'whitelist', 'limits', 'timeouts', 'redirect', 'retry', 'basicAuth', 'littleproxy'];

    protected $requiredFields = ['host'];

    protected $response;

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
            if (true === (bool) $this->config['autostart']) {
                $this->openProxy();
            }
        }
    }

    protected static function __pingProxy($url)
    {
        try {
            $response = Requests::get('http://'.$url.'/proxy/');
        } catch (\Exception $e) {
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
                    switch ((string) $config) {
                        case 'blacklist':
                            foreach ($data['patterns'] as $pattern) {
                                $response = $this->_blacklist($pattern, $data['code']);
                                if (isset($response->success)) {
                                    if (false === $response->success) {
                                        break;
                                    }
                                }
                            }
                            break;
                        case 'whitelist':
                            $patterns = implode(',', $data['patterns']);
                            $response = $this->_whitelist($patterns, $data['code']);
                            if (isset($response->success)) {
                                if (false === $response->success) {
                                    break;
                                }
                            }
                            break;
                        case 'limits':
                            $response = $this->_limits($data);
                            break;
                        case 'timeouts':
                            $response = $this->_timeouts($data);
                            break;
                        case 'redirect':
                            foreach ($data as $entry) {
                                $response = $this->_remapHosts($entry['address'], $entry['ip']);
                                if (isset($response->success)) {
                                    if (false === $response->success) {
                                        break;
                                    }
                                }
                            }
                            break;
                        case 'retry':
                            $response = $this->_retry($data);
                            break;
                        case 'basicAuth':
                            foreach ($data as $entry) {
                                $response = $this->_basicAuth($entry['domain'], $entry['options']);
                                if (isset($response->success)) {
                                    if (false === $response->success) {
                                        break;
                                    }
                                }
                            }
                            break;
                        default:
                            // do nothing
                    }
                }
            } catch (\Exception $e) {
                throw new ModuleConfigException(__CLASS__, $e->getMessage());
            }

            if (get_class($response) === 'Request')
            {
                $this->response = $response;
                if (false === $response->success) {
                    throw new ModuleConfigException(__CLASS__, "Proxy response error '{$response->status_code}' {$respone->body}");
                }
            }
        }
    }

    public function getProxyPort()
    {
        return $this->bmp->port;
    }

    public function openProxy($capabilities = null)
    {
        try {
            $this->bmp->open();
            if (empty($capabilities)) {
                $capabilities = $this->config;
            }
            $this->__setProxyCapabilities($capabilities);
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
        return $this->getProxyPort();
    }

    public function closeProxy()
    {
        try {
            $this->bmp->close();
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    public function startHar()
    {
        try {
            $this->response = $this->bmp->newHar();
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
        return $this->response->success;
    }

    public function startPage()
    {
        try {
            $this->response = $this->bmp->newPage();
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
        return $this->response->success;
    }

    public function getHar()
    {
        try {
            return $this->bmp->har;
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    public function getLimit()
    {
        try {
            return $this->bmp->getLimit();
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    // magic function that exposes BrowserMobProxy API pulic methods
    public function __call($method, $args)
    {
        // check if is a command call
        if (preg_match('/^_[A-z]+$/', $method)) {
            // extract standard method name
            $method = preg_filter('/_/', '', $method);
            // set call array for calling method
            $call = array($this->bmp, $method);
            // check if method is callable
            if (is_callable($call)) {
                $ret = call_user_func_array($call, $args);
            } else {
                throw new RuntimeException("Method ${method} does not exist or is not callable");
            }
        } else {
            throw new RuntimeException("Method ${method} does not exist or is not callable");
        }
        $ret = (isset($ret)) ? $ret : null;
        return $ret;
    }

}
