---
layout: post
date: 2013-06-07 18:21:13
title: 用Alfred2推送内容到手机
tags: alfred push
---

写了个`Alfred2`的workflow，用于推送文字内容到手机上，方便比较零碎的消息备忘。

实际代码仅一行，用的服务是“[消息速递](http://1290.me/)”


1. 安装“[消息速递](http://1290.me/)”，在设置里面找到“速递号”
2. 双击导入Push-Alfred2-Workflow
3. 双击中间的“/bin/bash”修改其中的domains参数为你自己的速递号
4. 在Alfred2中输入 push fuck！
5. 然后你就收到“fuck!”了

默认账号是我自己的，请大家修改为自己的账号，别给我发垃圾信息。。。


截图：
关键词 `push`

![push](/pic/alfred-push.png)

回车后稍等，可以看到成功提示（直接显示返回信息了）

![pushnotify](/pic/alfred-push-notify.png)


地址：
<https://github.com/henter/Push-Alfred2>

