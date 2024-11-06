# Domain (PHP)
Console tool for domain info fetching.

## Main features
- IP / Hostname
- Mailserver
- DNS fetch
- HTTP headers fetch

## Requirements
- working PHP in console mode

## Support OS
- Windows
- Linux

## Example
```
% domain neor.digital
```
```
domain:     neor.digital
ip:         5.45.114.184
hostname:   s20.neor.ee
hosting:    neor.ee
mailserver: mail.neor.digital -> service: neor.digital

DNS:  [45]
  SOA dns.fastdns24.com support.fastvps.hosting

Redirect: no

URL:  http://neor.digital  Duration: 0.05 sec   SSL: no
Headers:  [199]
  HTTP/1.1 200 OK
  Date: Wed, 06 Nov 2024 16:39:56 GMT
  Server: Apache
  X-Mod-Pagespeed: 1.13.35.2-0
  Cache-Control: max-age=0, no-cache
  Connection: close
  Content-Type: text/html; charset=UTF-8
```
