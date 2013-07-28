---
layout: post
date: 2013-07-18 16:19:56
title: Python实现APN推送
description: 用Python实现APN推送
tags: python APN 推送
---

Github上有很多Python实现的APN库，如PyAPNs这类，可以直接用，不过前不久在stackoverflow上找到一个原生的实现。


我简化了一些放到gist上了。
<script src="https://gist.github.com/henter/6097853.js"></script>

详细可以看这里：<https://gist.github.com/henter/6097853>


代码比较简单，不多说。
可能碰到的问题是证书文件，就是代码中的pem文件。

获取这个文件的步骤如下：

需要两个`p12`文件，如下


详细：<http://stackoverflow.com/questions/5833642/python-apns-sslerror>

> Apple Development Push Services certificate as cert.p12
> 
> primary key under Apple Development Push Services as pkey.p12


生成p12对应的pem文件

	$ openssl pkcs12 -in pkey.p12 -out pkey.pem -nodes -clcerts
	$ openssl pkcs12 -in cert.p12 -out cert.pem -nodes -clcerts

合并得到最终的pem文件

	$ cat cert.pem pkey.pem > iphone_ck.pem
	

最终的`iphone_ck.pem`就是代码中需用到的pem文件


现在基本的推送功能实现了，后面再说说结合队列处理异步推送等问题。
