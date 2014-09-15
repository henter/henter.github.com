---
layout: post
date: 2014-09-15 14:56:23
title: PHP调试工具ChromePHP简直太坑爹
description: 浪费了两个小时才确定是这个调试工具的问题
tags: php 调试 chromephp nginx 502 坑 symfony redis
---

昨天在开发一新接口时，遇到一问题，nginx时不时出现502错。

打开nginx错误日志，发现如下报错：

```
2014/09/15 14:34:28 [error] 5103#0: *114 upstream sent too big header while reading response header from upstream, client: 127.0.0.1, ...

```

这个属于比较常规的问题了，header长度超出，[之前遇到过](http://henter.me/post/nginx-error-upstream-sent-too-big-header-while-reading-response-header-from-upstream.html)

```
    fastcgi_buffer_size 128k;
    fastcgi_buffers 4 256k;
    fastcgi_busy_buffers_size 256k;
```
于是按照之前的方法适当调整nginx配置，发现问题依旧。

程序逻辑比较简单，大致流程是：

- 检查redis缓存看是否有数据，有则直接返回
- 否则查询数据库
- 写入redis缓存
- 返回数据

调试发现，每次写入redis缓存都会导致nginx报502

开始觉得不可能，因为系统其它很多地方也用到redis但一直没遇到过这个问题.

于是怀疑是不是`snc redis`的bug？
经过一番调试发现，如果执行redis命令的时候不记录日志，则能正常工作，否则就报502错，代码为下面的`$this->logger->logCommand`这一行。

```
    public function __call($name, array $arguments)
    {
        $log = true;

		...

        $startTime = microtime(true);
        $result = call_user_func_array(array($this->redis, $name), $arguments);
        $duration = (microtime(true) - $startTime) * 1000;

        if ($log && null !== $this->logger) {
            $this->logger->logCommand($this->getCommandString($name, $arguments), $duration, $this->alias, false);
        }

        return $result;
    }
```



进一步debug，发现这里调用到了`Symfony`内的`Monolog`的方法用于记录日志。


```
    public function addRecord($level, $message, array $context = array())
    {
		...

        // check if any handler will handle this message
        $handlerKey = null;
        foreach ($this->handlers as $key => $handler) {
            if ($handler->isHandling($record)) {
                $handlerKey = $key;
                break;
            }
        }
        // none found
        if (null === $handlerKey) {
            return false;
        }

        // found at least one, process message and dispatch it
        foreach ($this->processors as $processor) {
            $record = call_user_func($processor, $record);
        }
        while (isset($this->handlers[$handlerKey]) &&
            false === $this->handlers[$handlerKey]->handle($record)) {
            $handlerKey++;
        }

        return true;
    }
```

问题就出在上面的 `handlers`里，打印出来发现这里面包含了4个handler，经过排查发现是`ChromePhpHandler`的问题，进一步抠代码：

```
    public function onKernelResponse(FilterResponseEvent $event)
    {
		...
		
        $this->response = $event->getResponse();
        foreach ($this->headers as $header => $content) {
            $this->response->headers->set($header, $content);
        }
        $this->headers = array();
    }
```

打印出这里的header后发现问题了，header内的`X-ChromeLogger-Data`值长度达到`165952`字节，大约`162K`，超出了我本地nginx配置里设定的128K，于是出现502. 如图：

![chromephp-headers](/pic/chromephp-headers.png)

为弄清具体是什么数据，经过`base64_decode`和`json_decode`后，如图：

![chromephp-headers-print](/pic/chromephp-headers-print.png)

原来是`snc_redis`完整记录了`redis`操作数据，所以如果写入到redis的数据过大，可能超出web服务器设置的header最大长度，导致报错。

这尼玛。。这种方式简直反人类啊，绝逼不靠谱，于是准备跟`symfony`官方反馈这个问题，不过发现[官方已经解决过了](https://github.com/symfony/symfony-standard/pull/569/files)。。 
解决办法是，去掉Symfony配置文件`config_dev.yml`内的`chromephp`相关配置。

到这里问题解决了，简直太坑。。


不过，调试过程中发现`snc redis`的另外一个坑，下次有空再写出来。

