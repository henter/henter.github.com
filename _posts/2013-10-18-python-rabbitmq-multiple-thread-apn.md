---
layout: post
date: 2013-12-18 15:19:23
title: Python+RabbitMQ实现多线程APN推送
description: 用Python结合RabbitMQ实现多线程APN推送
tags: python RibbitMQ APN 推送
---

这篇是用来填坑的，填之前的一篇[Python实现APN推](/post/python-apn.html)提到的坑。。
之前的apn推送实现方式比较原始，没有做容错、超时等处理，只能将就着用，现在做了些改进，另外，结合RabbitMQ队列实现多线程推送。

代码已发到<https://github.com/henter/PythonRabbitMQAPN>上


主要分为4个文件

	receive.py 			连接rabbitmq，开启多线程
	thread_manager.py 	仅仅为开启多线程做调用
	push_ios.py 		推送
	apns.py  			推送接口

实现代码比较搓，大家将就着看吧

另外，[`pika`](https://pika.readthedocs.org/en/latest/faq.html)这个库官方并不推荐用于多线程环境，推荐在每个线程内单独连接RabbitMQ，导致的结果就是，如果开启比较多线程，会产生大量的RabbitMQ连接，CPU占用比较高。


>Pika does not have any notion of threading in the code. If you want to use Pika with threading, make sure you have a Pika connection per thread, created in that thread. It is not safe to share one Pika connection across threads.


这种实现方式只能算是勉强够用，线程数量是根据队列内的消息数量动态控制的，推送高峰期会产生比较多的连接，会断断续续产生rabbitmq连接失败的问题，平时基本稳定。

APN推送峰值大概为300条每秒（开启100个线程时），效率比较低。


代码如下：

<script src="https://gist.github.com/henter/8748092.js"></script>



PS:

后来也尝试过[`rabbitpy`](https://github.com/gmr/rabbitpy)这个库(作者也是`pika`的维护者)，号称是线程安全。
不过测试过程中发现另外一个问题，在同一线程内只能同时用一个Consumer，[否则会报错](https://github.com/gmr/rabbitpy/issues/12)。

测试下来虽然没有前面的报错，不过两者性能相差不大，最终还是没有用这个库。

后面打算用NodeJS重写这套推送系统。

