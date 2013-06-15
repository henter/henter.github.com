---
date: 2013-03-29 15:55:54
layout: post
title: 解决PHP Maximum function nesting level of '100' reached
tags: php xdebug
---

PHP报错 `“Maximum function nesting level of 100 reached”`的解决方法

导致报错的原因是：使用递归函数时，递归次数超过了100

实际上这个不是PHP本身的问题，是由于`xdebug`的默认设置导致的。

解决办法，修改`php.ini`文件

	xdebug.max_nesting_level = 200
