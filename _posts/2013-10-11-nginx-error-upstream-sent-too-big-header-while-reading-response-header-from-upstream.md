---
layout: post
date: 2013-10-11 23:43:45
title: 修复Nginx报错：upstream sent too big header while reading response header from upstream
tags: nginx php
---


今天调试symfony时发现一直502，日志里面有如下报错：

	2013/10/11 23:18:44 [error] 51982#0: *1 upstream sent too big header while reading response header from upstream, client: 127.0.0.1, server: work.cc, request: "POST /api/user/register HTTP/1.1", upstream: "fastcgi://127.0.0.1:9000", host: "test.work.cc", referrer: "http://test.work.cc/api/doc/"
	
google一番，解决方法如下：

在 `nginx.conf` 的http段，加入下面的配置：
	
	proxy_buffer_size  128k;
	proxy_buffers   32 32k;
	proxy_busy_buffers_size 128k;

重启后一般就可以解决，
如果还是报502，再在host配置的php段加入下面配置：

	fastcgi_buffer_size 128k;
	fastcgi_buffers 4 256k;
	fastcgi_busy_buffers_size 256k;

重启nginx即可。

参考：

http://hi.baidu.com/wastorode/item/ec86ade6ac0af7a2c10d75f4

http://stackoverflow.com/questions/13894386/upstream-too-big-nginx-codeigniter


