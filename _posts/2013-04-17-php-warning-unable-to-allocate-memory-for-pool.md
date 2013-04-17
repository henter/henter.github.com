---
layout: post
date: 2013-04-17 17:10:04
title: 解决PHP报错：Unable to allocate memory for pool
tags: php
---


之前也遇到过几次，一直懒得记，今天记录一下方便查阅。

报错信息大致为：

	Unable to allocate memory for pool. in xxxfile.php on line 123

####原因：

实际上这是APC配置问题导致的，与PHP本身无关。

	This error is usually related to Alternative PHP Cache (APC). 
	APC is a free and open opcode cache for PHP. Its goal is to provide a free, open, and robust framework for caching and optimizing PHP intermediate code.

####解决：
修改`apc.ini`文件，可以通过`phpinfo()`查看此文件的位置。
也可以。。偷懒如下：

	➜  ~  locate apc.ini
	/usr/local/etc/php/5.4/conf.d/ext-apc.ini
	
	然后
	➜  ~  vim `locate apc.ini`
	
找到其中的

	apc.shm_size=64M

将`64M`改大一点就可以了（我机器上默认的是64M，然后我直接改为164M了。）


保存，重启php，ok了。


参考：<http://www.cyberciti.biz/faq/linux-unix-php-warning-unable-to-allocate-memory-for-pool/>
