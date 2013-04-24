---
layout: post
date: 2013-04-24 11:19:56
title: Python备忘录
description: 记录python使用过程中的各种问题
tags: python
---


记录python使用过程中的各种问题，好记性不如烂博客。


SyntaxError: Non-ASCII character '\xe5' in file
----------
这个错误是python的默认编码文件是用的ASCII码，
将文件存成了UTF-8也没用，解决办法很简单，只要在文件开头加入

	# -*- coding: UTF-8 -*-
	
或者

	#coding=utf-8 

就能解决问题了。


<br />

字符串处理
---
常用

	str1 + str2 连接
	len(str) 长度
	str.trip() 去空格
	str.lstrip()
	str.rtrip()
	
	str.replace(old, new[, maxtimes]) 替换
	str[from, to] 截取
	
	'1,2,3,4,5'.split(',') 分割
	
	

判断

	str.startwith()
	str.endwith()
	str.upper()
	str.lower()
	str.capitalize()
	str.isalnum() 是否全字母或数字
	str.isalpha() 是否全字母
	str.isdigit() 是否全数字
	str.islower()
	str.isupper()
	
	'a' in 'abc'  True
	
查找

	 str.find(needle)
	 str.find(needle, start, end)
	 str.rfind(needle)
	 str.count(needle)
	 
	 尽量不要用 str.index(needle)，找不到时会抛异常，find返回-1
	 


<br /><br />
参考：

<http://sjolzy.cn/Python-built-in-string-handling-functions-order.html>