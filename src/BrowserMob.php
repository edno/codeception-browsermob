<?php
namespace Codeception\Extension;

use Codeception\Module;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;
use \PHPBrowserMobProxy_Client as BMP;
use \Requests;
use \RuntimeException;

/**
 * @method void _open() Open a new proxy using the PHPBrowserMobProxy_Client method
 * @method void _close() Close current proxy using the PHPBrowserMobProxy_Client method
 * @method \Requests_Response _newHar(string $label='') Start new HAR using the PHPBrowserMobProxy_Client method
 * @method \Requests_Response _newPage(string $label='') Start new HAR page using the PHPBrowserMobProxy_Client method
 * @method \Requests_Response _blacklist(string $regexp, integer $status_code) Blacklist URLs using the PHPBrowserMobProxy_Client method
 * @method \Requests_Response _whitelist(string $regexp, integer $status_code) Whitelist URLs using the PHPBrowserMobProxy_Client method
 * @method \Requests_Response _basicAuth(string $domain, string[] $options) Set HTTP authentication headers using the PHPBrowserMobProxy_Client method
 * @method \Requests_Response _headers(string[] $options) Override requests HTTP headers using the PHPBrowserMobProxy_Client method
 * @method \Requests_Response _responseInterceptor(string $js) Intercept HTTP responses using the PHPBrowserMobProxy_Client method
 * @method \Requests_Response _requestInterceptor(string $js) Intercept HTTP requests using the PHPBrowserMobProxy_Client method
 * @method \Requests_Response _limits(string[] $options) Set proxy limits using the PHPBrowserMobProxy_Client method
 * @method \Requests_Response _timeouts(string[] $options) Set proxy timeouts using the PHPBrowserMobProxy_Client method
 * @method \Requests_Response _remapHosts(string $address, string $ip_address) Map hosts to IP using the PHPBrowserMobProxy_Client method
 * @method \Requests_Response _waitForTrafficToStop(integer $quiet_period, integer $timeout) Wait for traffic before stopping proxy using the PHPBrowserMobProxy_Client method
 * @method string _clearDnsCache() Flux proxy DNS cache using the PHPBrowserMobProxy_Client method
 * @method \Requests_Response _rewriteUrl(string $match, string $replace) Rewrite URLs using the PHPBrowserMobProxy_Client method
 * @method \Requests_Response _retry(integer $retry_count) Set proxy retries using the PHPBrowserMobProxy_Client method
 */
class BrowserMob extends Module
{

    /**
     * @var string[] $config
     */
    protected $config = ['host', 'port', 'autostart', 'blacklist', 'whitelist', 'limits', 'timeouts', 'dns', 'retry', 'basicAuth', 'littleproxy'];

    /**
     * @var string[] $requiredFields
     */
    protected $requiredFields = ['host', 'port'];

    /**
     * @var Requests $response
     */
    protected $response;

    /**
     * @var PHPBrowserMobProxy_Client $bmp
     */
    private $bmp;

    /**
     * @codeCoverageIgnore
     * @ignore Codeception specific
     */
    public function _initialize()
    {
        $host = $this->config['host'].':'.$this->config['port'];

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

    /**
     * Verify if the BrowserMobProxy is reachable
     *
     * @param string $host BrowserMob Proxy host, format host:port
     *
     * @return boolean Returns true if proxy available, else false
     */
    protected static function __pingProxy($host)
    {
        try {
            $response = Requests::get('http://'.$host.'/proxy/');
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }

        return $response->success;
    }

    /**
     * Set proxy capabilitites: blacklist, whitelist, limits, timeouts, dns, retry, basicAuth
     *
     * @uses BrowserMob::_blacklist
     * @uses BrowserMob::_whitelist
     * @uses BrowserMob::_limits
     * @uses BrowserMob::_timeouts
     * @uses BrowserMob::_remapHosts
     * @uses BrowserMob::_retry
     * @uses BrowserMob::_basicAuth
     *
     * @param mixed[] $options Array of options (see extension configuration)
     *
     * @return void
     */
    protected function __setProxyCapabilities($options)
    {
        foreach ($options as $config => $data) {
            if (false === empty($data)) {
                switch ((string) $config) {
                    case 'blacklist':
                        foreach ($data['patterns'] as $pattern) {
                            $this->_blacklist($pattern, $data['code']);
                        }
                        break;
                    case 'whitelist':
                        $patterns = implode(',', $data['patterns']);
                        $this->_whitelist($patterns, $data['code']);
                        break;
                    case 'limits':
                        $this->_limits($data);
                        break;
                    case 'timeouts':
                        $this->_timeouts($data);
                        break;
                    case 'dns':
                        foreach ($data as $entry) {
                            $this->_remapHosts($entry['domain'], $entry['ip']);
                        }
                        break;
                    case 'retry':
                        $this->_retry($data);
                        break;
                    case 'basicAuth':
                        foreach ($data as $entry) {
                            $this->_basicAuth($entry['domain'], $entry['options']);
                        }
                        break;
                    default:
                        // do nothing
                }
            }
        }
    }

    /**
     * Return current proxy port opened on BrowserMobProxy
     *
     * @return integer Proxy port
     */
    public function getProxyPort()
    {
        return $this->bmp->port;
    }

    /**
     * Open a new proxy on BrowserMobProxy
     *
     * @see BrowserMob::__setProxyCapabilities
     *
     * @uses BrowserMob::_open
     * @uses BrowserMob::__setProxyCapabilities
     * @uses BrowserMob::getProxyPort
     *
     * @param mixed[]|null $capabilities Array of capabilities. Use extension configuration if null
     *
     * @return integer Proxy port
     */
    public function openProxy($capabilities = null)
    {
        $this->_open();
        if (empty($capabilities)) {
            $capabilities = $this->config;
        }
        $this->__setProxyCapabilities($capabilities);
        return $this->getProxyPort();
    }

    /**
     * Close the current proxy opened BrowserMobProxy
     * Not supported by library chartjes/php-browsermob-proxy
     *
     * @uses BrowserMob::_close
     *
     * @return void
     */
    public function closeProxy()
    {
        $this->_close();
    }

    /**
     * Start to capture the HTTP archive
     *
     * @uses BrowserMob::_newHar
     *
     * @param string|null $label Title of first HAR page
     *
     * @return boolean Command status
     */
    public function startHar($label = '')
    {
        $this->_newHar($label);
        return $this->response->success;
    }

    /**
     * Add a new page to the HTTP archive
     *
     * @uses BrowserMob::_newPage
     *
     * @param string|null $label Title of new HAR page
     *
     * @return boolean Command status
     */
    public function addPage($label = '')
    {
        $this->_newPage($label);
        return $this->response->success;
    }

    /**
     * Get the HTTP archive captured
     *
     * @return mixed[] HTTP archive
     */
    public function getHar()
    {
        return $this->bmp->har;
    }

    /**
     * Override HTTP request headers
     *
     * @uses BrowserMob::_headers
     *
     * @param string[] $headers Array of HTTP headers
     *
     * @return boolean Command status
     */
    public function setHeaders($headers)
    {
        $this->_headers($headers);
        return $this->response->success;
    }

    /**
     * Rewrite URLs with regex
     *
     * @uses BrowserMob::_rewriteUrl
     *
     * @param string $match Matching URL regular expression
     * @param string $replace Replacement URL
     *
     * @return boolean Command status
     */
    public function redirectUrl($match, $replace)
    {
        $this->_rewriteUrl($match, $replace);
        return $this->response->success;
    }

    /**
     * Run Javascript against requests before sending them
     *
     * @param string $script Javascript code
     *
     * @return boolean Command status
     */
    public function filterRequest($script)
    {
        $this->_requestInterceptor($script);
        return $this->response->success;
    }

    /**
     * Run Javascript against responses received
     *
     * @param string $script Javascript code
     *
     * @return boolean Command status
     */
    public function filterResponse($script)
    {
        $this->_responseInterceptor($script);
        return $this->response->success;
    }

    /**
     * Magic function that exposes BrowserMobProxy API pulic methods
     *
     * @ignore Exclude from documentation
     */
    public function __call($name, $args)
    {
        // check if is a command call
        if (preg_match('/^_[A-z]+$/', $name)) {
            // extract standard method name
            $name = preg_filter('/_/', '', $name);
            // set call array for calling method
            $call = array($this->bmp, $name);
            // check if method is callable
            if (is_callable($call)) {
                $ret = call_user_func_array($call, $args);
                if (get_class($ret) === 'Requests_Response') {
                    $this->response = $ret;
                    if (false === $ret->success) {
                        throw new ModuleConfigException(__CLASS__, "Proxy response error '{$ret->status_code}' {$ret->body}");
                    }
                }
            } else {
                throw new RuntimeException("Method ${name} does not exist or is not callable");
            }
        } else {
            throw new RuntimeException("Method ${method} does not exist or is not callable");
        }
        $ret = (isset($ret)) ? $ret : null;
        return $ret;
    }

}
