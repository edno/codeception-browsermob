# Codeception Browser Mob Proxy

[![Latest Version](https://img.shields.io/packagist/v/edno/codeception-browsermob.svg?style=flat-square)](https://packagist.org/packages/edno/codeception-browsermob)
[![Dependency Status](https://www.versioneye.com/user/projects/577d5f3991aab50034283ef2/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/577d5f3991aab50034283ef2)
[![Build Status](https://img.shields.io/travis/edno/codeception-browsermob.svg?style=flat-square)](https://travis-ci.org/edno/codeception-browsermob)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/8c19ed7d-40e6-41ce-b9c7-fb2a87096103.svg?style=flat-square)](https://insight.sensiolabs.com/projects/8c19ed7d-40e6-41ce-b9c7-fb2a87096103)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/edno/codeception-browsermob.svg?style=flat-square)](https://scrutinizer-ci.com/g/edno/codeception-browsermob/?branch=master)
[![Coverage Status](https://img.shields.io/coveralls/edno/codeception-browsermob.svg?style=flat-square)](https://coveralls.io/github/edno/codeception-browsermob?branch=master)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://raw.githubusercontent.com/edno/codeception-secureshell/master/LICENSE)

The [Codeception](http://codeception.com/) module for [BrowserMob Proxy](http://bmp.lightbody.net/).

## Roadmap
- [x] **0.1**: Initial version based on library [PHPBrowserMobProxy by chartjes](https://github.com/chartjes/PHPBrowserMobProxy/) with limited support of BrowserMob Proxy legacy API and no Littleproxy support.
- [ ] 0.2: New PHP BrowserMob Proxy with full REST API support (Jetty and Littleproxy) and [proxy auto-configuration](https://en.wikipedia.org/wiki/Proxy_auto-config) feature.

## Minimum Requirements
- Codeception 2.2
- PHP 5.5
- BrowserMob Proxy 2.0

## Installation
The module can be installed using [Composer](https://getcomposer.org).

```bash
$ composer require edno/codeception-browsermob
```

Be sure to enable the module as shown in
[configuration](#configuration) below.

## Configuration
Enabling **BrowserMob** is done in your configuration file `.yml`.

```yaml
module:
    enabled:
        - Codeception\Extension\BrowserMob
            host: 'localhost'
            port: 8080
```

### Parameters
**BrowserMob** support following configuration parameters:

| Parameter | Type | Description |
|-----------|------|-------------|
| `host` | **string** | BrowserMob Proxy host |
| `port` | **integer** | BrowserMob Proxy port |
| `autostart` | **boolean** | Start a new proxy instance automatically [*default = false*] |
| `whitelist` | **array** | <p>URLs whitelisting</p><p>`code`: HTTP status code for URLs that do not match patterns</p><p>`patterns`: array of whitelisted URLs patterns<p> |
| `blacklist` | **array** | <p>URLs blacklisting</p><p>`code`: HTTP status code for URLs that match patterns</p><p>`patterns`: array of blacklisted URLs patterns<p> |
| `limits` | **array** | Bandwidth limits through the proxy (see [BrowserMob Proxy REST API](https://github.com/lightbody/browsermob-proxy#rest-api))|
| `timeouts` | **array** | Proxy timeouts (see [BrowserMob Proxy REST API](https://github.com/lightbody/browsermob-proxy#rest-api)) |
| `basicAuth` | **array** | <p>Sets automatic basic authentication a list of domains</p><p>`domain`: domain name</p><p>`options`:HTTP authentication parameters using format *`name: value`* (see [BrowserMob Proxy REST API](https://github.com/lightbody/browsermob-proxy#rest-api))</p> |
| `dns` | **array** | <p>Internal proxy DNS using pairs of domain/IP to map</p><p>`domain`: domain name</p><p>`ip`: matching IP</p>  |
| `retry` | **integer** | Number of times a method will be retried [*default = 0*] |

***Example***
```yaml
modules:
    config:
        Codeception\Extension\BrowserMob:
            host: 'localhost'
            port: 9090
            autostart: true
            whitelist:
                code: 404
                patterns:
                    - 'http://codeception.com/'
            limits:
                downstreamKbps: 12
                upstreamKbps: 12
                latency: 1
            timeouts:
                request: 10
                read: 10
                connection: 10
                dns: 10
            basicAuth:
                - domain: example.local
                  options:
                      username: myUsername
                      password: myPassword
            dns:
                - domain: example.local
                  ip: 127.0.0.1
                - domain: wikipedia.org
                  ip: 192.168.1.1
            retry: 3
```

## Documentation
The module documentation is available in the [wiki](https://github.com/edno/codeception-browsermob/wiki/Codeception-BrowserMob-Proxy-extension-Documentation).

For more information on how to use BrowserMob proxy, refer to [BrowserMob Proxy REST API documentation](https://github.com/lightbody/browsermob-proxy#rest-api).
