---
layout: post
date: 2014-01-31 15:19:23
title: 用Node.js结合RabbitMQ实现APN推送
description: 用Node.js结合RabbitMQ实现APN推送，重写了之前的python多线程实现版本
tags: node RibbitMQ APN 推送
---


最近刚开始看nodejs，于是想把之前写得稀烂的[Python+RabbitMQ实现多线程APN推送](/post/python-rabbitmq-multiple-thread-apn.html)推送重写一次。

实际完成后代码量很少，用到了两个库 [node-apn](https://github.com/argon/node-apn) 和 [node-amqp](https://github.com/postwait/node-amqp)，我要做的只是将两者结合起来打打酱油。
<br />
<br />


####需要完善的部分：

rabbitmq的ack机制

现在仅仅是拿到消息调用推送函数后就返回ack了，实际上此时并不确定是否推送成功了（因为是异步），所以应该是在推送的`transmitted`事件触发后再返回ack。


####准备工作：

    安装rabbitmq
    拿到推送证书 cert.pem和key.pem
    修改receive.js中的队列名称

####运行：

    npm install
    node receive.js
    
    
demo代码已发到<https://github.com/henter/NodeRabbitMQAPN>上


APN推送峰值大概为2000条每秒，完爆之前python多线程的实现方式。

