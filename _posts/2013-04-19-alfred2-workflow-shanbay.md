---
layout: post
date: 2013-04-19 18:21:13
title: 用python写了个扇贝词典的Alfred2插件
tags: alfred 扇贝 python
---

前不久才知道有`Alfred2`这么一个神器，工作效率大大提升。

关于这款软件就不多介绍了。

今天写了个用于查询扇贝网词典的workflow，发布在github上了。

关键词：`sb` 哈哈～

![demo](/pic/shanbay-alfred-demo.png)

还不是很完善，主要因为扇贝比较变态，必须登陆才能查词，用到了requests模块，但是这个模块不是python自带的，需要手动安装

也就是说现在这个workflow还无法推出去给网友用。。改天有空改一下，去掉requests模块。


后面会加上添加生词到单词本功能。

地址：
<https://github.com/henter/Shanbay-Alfred2>

